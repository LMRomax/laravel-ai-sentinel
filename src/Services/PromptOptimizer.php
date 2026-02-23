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
     * @param string $prompt The original prompt to optimize
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
     * @param string $text The input text to compress
     * @return string The compressed text
     */
    protected function compress(string $text): string
    {
        if (! config('ai-guard.optimization.enable_compression', true)) {
            return $text;
        }

        // -------------------------------
        // 0) Normalisation préliminaire
        // -------------------------------
        $text = preg_replace('/\s+/u', ' ', trim($text));
        $text = preg_replace('/([.!?])\1+/u', '$1', $text);

        if ($text === '') {
            return $text;
        }

        // -------------------------------
        // 1) Découpage en phrases
        // -------------------------------
        $sentences = preg_split('/(?<=[.!?。！？])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (! $sentences || count($sentences) === 1) {
            return $text;
        }

        // -------------------------------
        // 2) Extraction du "topic principal"
        // -------------------------------
        preg_match_all('/\b[\p{L}\d]{3,}(?:\s+[\p{L}\d]{3,}){1,5}\b/iu', $text, $matches);
        $candidates = $matches[0] ?? [];

        if (empty($candidates)) {
            $mainTopic = mb_strtolower($sentences[0], 'UTF-8');
        } else {
            usort($candidates, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));
            $mainTopic = mb_strtolower(trim($candidates[0]), 'UTF-8');
        }

        // -------------------------------
        // 3) Scoring des phrases
        // -------------------------------
        $scored = [];

        foreach ($sentences as $index => $sentence) {
            $s = trim($sentence);
            if ($s === '') continue;

            $sLower = mb_strtolower($s, 'UTF-8');

            $topicBoost = str_contains($sLower, $mainTopic) ? 2.5 : 0;

            $words   = preg_split('/\s+/u', $sLower);
            $unique  = array_unique($words);
            $density = count($unique) / max(count($words), 1);

            $lenScore = min(mb_strlen($s, 'UTF-8') / 300, 0.2);
            $posBoost = $index === 0 ? 0.2 : 0;

            $totalScore = $density + $lenScore + $topicBoost + $posBoost;

            $scored[] = [
                'index'    => $index,
                'sentence' => $s,
                'score'    => $totalScore,
            ];
        }

        // -------------------------------
        // 4) Sélection des phrases importantes
        // -------------------------------
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        // Nombre de phrases gardées : 2 ou 3 max
        $top = array_slice($scored, 0, 3);
        usort($top, fn($a, $b) => $a['index'] <=> $b['index']);

        $summary = implode(' ', array_map(fn($s) => $s['sentence'], $top));

        // -------------------------------
        // 5) Nettoyage final orienté "prompt"
        // -------------------------------

        // Virer le small talk au début
        $summary = preg_replace(
            '/^(hi|hello|hey|salut|coucou)[^a-zA-Z0-9]+/iu',
            '',
            $summary
        );

        // Virer les excuses / remerciements de fin + tout ce qui suit
        $summary = preg_replace(
            '/\b(sorry if this is long|sorry if this is|i know i\'m rambling|i know I\'m rambling|thank you so much|merci beaucoup)\b.*$/iu',
            '',
            $summary
        );

        // Nettoyage des double virgules/espaces
        $summary = preg_replace('/\s+([,.!?])/', '$1', $summary);
        $summary = preg_replace('/[,.]{2,}/', '$0[0]', $summary);
        $summary = preg_replace('/\s+/u', ' ', $summary);

        // Trim final propre (éviter virgule/point au début/fin)
        $summary = trim($summary, " \t\n\r\0\x0B,.");

        $summary = $this->postProcessPrompt($summary);

        return $summary;
    }

    /**
     * Post-process the optimized prompt to further reduce tokens by removing common fillers, greetings, and apologies.
     * This is a more aggressive cleanup step that can be applied after the main optimization to squeeze out
     * any remaining fluff that doesn't add value to the prompt. It targets common patterns in both English and French.
     * 
     * @param string $text The input text to post-process
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
     * @param string $context The input context to truncate
     * @param int|null $maxTokens Optional max tokens to fit within (defaults to config value)
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
