<?php

namespace Lmromax\LaravelAiGuard\Services;

class PromptOptimizer
{
    protected CostCalculator $costCalculator;

    public function __construct()
    {
        $this->costCalculator = new CostCalculator;
    }

    /**
     * Optimize a prompt to reduce token usage
     *
     * @param  string  $prompt  The original prompt to optimize
     * @return array An array containing original, optimized, tokens saved, and compression ratio
     */
    public function optimize(string $prompt): array
    {
        if (! config('ai-guard.optimization.enabled', true)) {
            return [
                'original' => $prompt,
                'optimized' => $prompt,
                'tokens_saved' => 0,
                'compression_ratio' => 0,
            ];
        }

        $original = $prompt;
        $optimized = $this->compress($prompt);

        $tokensOriginal = $this->costCalculator->estimateTokens($original);
        $tokensOptimized = $this->costCalculator->estimateTokens($optimized);
        $tokensSaved = $tokensOriginal - $tokensOptimized;

        return [
            'original' => $original,
            'optimized' => $optimized,
            'tokens_original' => $tokensOriginal,
            'tokens_optimized' => $tokensOptimized,
            'tokens_saved' => $tokensSaved,
            'compression_ratio' => $tokensOriginal > 0
                ? round(($tokensSaved / $tokensOriginal) * 100, 2)
                : 0,
        ];
    }

    /**
     * Compress text to reduce tokens
     *
     * @param  string  $text  The input text to compress
     * @return string The compressed text
     */
    /**
     * Compress text to reduce tokens
     *
     * @param  string  $text  The input text to compress
     * @return string The compressed text
     */
    protected function compress(string $text): string
    {
        if (!config('ai-guard.optimization.enable_compression', true)) {
            return $text;
        }

        // Clean and normalize
        $text = preg_replace('/\s+/u', ' ', trim($text));

        if (mb_strlen($text, 'UTF-8') < 100) {
            return $text; // Too short to compress meaningfully
        }

        // Split into sentences
        $sentences = $this->splitIntoSentences($text);

        if (count($sentences) <= 2) {
            return $text; // Already concise
        }

        // Extract keywords using TF-IDF-like approach
        $keywords = $this->extractKeywords($text);

        // Score each sentence based on keyword presence
        $scored = $this->scoreSentences($sentences, $keywords);

        // Keep top 60% of sentences by score
        $keepCount = max(2, (int)ceil(count($scored) * 0.6));
        $topSentences = array_slice($scored, 0, $keepCount);

        // Restore original order
        usort($topSentences, fn($a, $b) => $a['index'] <=> $b['index']);

        $compressed = implode(' ', array_column($topSentences, 'sentence'));

        // Post-process
        $compressed = $this->cleanupCompressed($compressed);

        return $compressed;
    }

    /**
     * Split text into sentences (works for most languages)
     */
    protected function splitIntoSentences(string $text): array
    {
        // Universal sentence enders
        $pattern = '/(?<=[.!?。！？।।۔।])\s+/u';
        $sentences = preg_split($pattern, $text, -1, PREG_SPLIT_NO_EMPTY);

        return array_map('trim', $sentences);
    }

    /**
     * Extract important keywords using frequency and position
     */
    protected function extractKeywords(string $text): array
    {
        // Tokenize (works for most scripts)
        $words = preg_split('/[\s\p{P}]+/u', mb_strtolower($text, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY);

        // Count frequencies
        $freq = array_count_values($words);

        // Filter out very short words (likely stop words in any language)
        $freq = array_filter($freq, fn($word) => mb_strlen($word, 'UTF-8') >= 3, ARRAY_FILTER_USE_KEY);

        // Keep only words that appear multiple times OR are long (likely important)
        $keywords = [];
        foreach ($freq as $word => $count) {
            if ($count >= 2 || mb_strlen($word, 'UTF-8') >= 8) {
                $keywords[$word] = $count;
            }
        }

        // Sort by frequency
        arsort($keywords);

        // Return top 15 keywords
        return array_slice($keywords, 0, 15, true);
    }

    /**
     * Score sentences based on keyword density and position
     */
    protected function scoreSentences(array $sentences, array $keywords): array
    {
        $scored = [];
        $totalSentences = count($sentences);

        foreach ($sentences as $index => $sentence) {
            $sLower = mb_strtolower($sentence, 'UTF-8');

            // Count keyword occurrences in this sentence
            $keywordScore = 0;
            foreach ($keywords as $keyword => $freq) {
                if (mb_stripos($sLower, $keyword) !== false) {
                    $keywordScore += $freq;
                }
            }

            // Position bonus (first and last sentences often important)
            $positionScore = 0;
            if ($index === 0) {
                $positionScore = 2; // First sentence bonus
            } elseif ($index === $totalSentences - 1) {
                $positionScore = 1; // Last sentence bonus
            }

            // Length penalty (very short sentences often less informative)
            $length = mb_strlen($sentence, 'UTF-8');
            $lengthScore = ($length >= 20) ? 1 : 0;

            $totalScore = $keywordScore + $positionScore + $lengthScore;

            $scored[] = [
                'index' => $index,
                'sentence' => $sentence,
                'score' => $totalScore,
            ];
        }

        // Sort by score descending
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return $scored;
    }

    /**
     * Clean up compressed text
     * 
     * @var string $text
     * @return string
     */
    protected function cleanupCompressed(string $text): string
    {
        // Remove common filler phrases (multilingual)
        $fillers = [
            // English
            '/\b(please|kindly|if you (could|would|can)|I would like|could you please)\b/iu',
            '/\b(thank you|thanks|sorry|excuse me)\s*(so much|very much|in advance)?\b/iu',

            // French
            '/\b(s\'il vous plaît|s\'il te plaît|merci|désolé|excusez-moi)\b/iu',

            // Spanish  
            '/\b(por favor|gracias|lo siento|disculpe)\b/iu',

            // German
            '/\b(bitte|danke|entschuldigung)\b/iu',
        ];

        foreach ($fillers as $pattern) {
            $text = preg_replace($pattern, '', $text);
        }

        // Clean up whitespace
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = preg_replace('/\s+([,.!?])/u', '$1', $text);

        return trim($text);
    }

    /**
     * Post-process the optimized prompt to further reduce tokens by removing common fillers, greetings, and apologies.
     * This is a more aggressive cleanup step that can be applied after the main optimization to squeeze out
     * any remaining fluff that doesn't add value to the prompt. It targets common patterns in both English and French.
     *
     * @param  string  $text  The input text to post-process
     * @return string The post-processed text with reduced tokens
     */
    protected function postProcessPrompt(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return $text;
        }

        // 1) Enlever les salutations / small talk initiaux (en / fr)
        $text = preg_replace(
            '/^(hi|hello|hey|salut|coucou|bonjour)[^a-zA-Z0-9]+/iu',
            '',
            $text
        );

        // 2) Couper tout ce qui suit excuses / remerciements de fin
        $text = preg_replace(
            '/\b(sorry if this is long|sorry if this is|i know i\'m rambling|i know I\'m rambling|merci beaucoup|thank you so much|thanks a lot)\b.*$/iu',
            '',
            $text
        );

        // 3) Focus : on coupe tout ce qui précède la première vraie demande
        //    (explain / show / describe / help / explique / montre / décris / aide...)
        if (preg_match(
            '/\b(explain|show|describe|help|teach|guide|'
                . 'explique(?:r)?|montre(?:r)?|décris|aide(?:r)?)\b/iu',
            $text,
            $m,
            PREG_OFFSET_CAPTURE
        )) {
            $text = ltrim(substr($text, $m[0][1]));
        }

        // 4) Nettoyage des fillers classiques (en / fr)
        $text = preg_replace(
            '/\b(really|actually|basically|maybe|kind of|sort of|like|'
                . 'franchement|vraiment|un peu|genre|en fait)\b[, ]*/iu',
            ' ',
            $text
        );

        // 5) Nettoyage espaces + ponctuation
        $text = preg_replace('/\s+([,.!?])/', '$1', $text);   // espace avant ponctuation
        $text = preg_replace('/([,.!?])\s*([,.!?])/', '$1 ', $text); // double ponctuation
        $text = preg_replace('/\s+/u', ' ', $text);

        // 6) Trim final propre
        $text = trim($text, " \t\n\r\0\x0B,.");

        return $text;
    }

    /**
     * Truncate context to fit within token limit
     *
     * @param  string  $context  The input context to truncate
     * @param  int|null  $maxTokens  Optional max tokens to fit within (defaults to config value)
     * @return string The truncated context
     */
    public function truncateContext(string $context, ?int $maxTokens = null): string
    {
        $maxTokens = $maxTokens ?? config('ai-guard.optimization.max_context_tokens', 4000);

        $estimatedTokens = $this->costCalculator->estimateTokens($context);

        if ($estimatedTokens <= $maxTokens) {
            return $context;
        }

        // Rough truncation: keep ratio
        $ratio = $maxTokens / $estimatedTokens;
        $targetLength = (int) (strlen($context) * $ratio);

        return substr($context, 0, $targetLength) . '...';
    }
}
