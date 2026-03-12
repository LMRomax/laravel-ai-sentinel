<?php

namespace Lmromax\LaravelAiGuard\Services;

use Anthropic\Laravel\Facades\Anthropic;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class PromptOptimizer
{
    protected CostCalculator $costCalculator;

    public function __construct()
    {
        $this->costCalculator = new CostCalculator;
    }

    /**
     * Optimize a prompt to reduce token usage
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
     * Compress text using AI (intelligent, multilingual)
     */
    protected function compress(string $text): string
    {
        if (! config('ai-guard.optimization.enable_compression', true)) {
            return $text;
        }

        // Don't compress if already short
        if (mb_strlen($text, 'UTF-8') < 100) {
            return $text;
        }

        // Use AI-powered compression if available
        if (config('ai-guard.optimization.use_ai_compression', true)) {
            try {
                return $this->aiCompress($text);
            } catch (\Exception $e) {
                // Fallback to rule-based if AI compression fails
                Log::warning('AI compression failed, falling back to rules', [
                    'error' => $e->getMessage(),
                ]);

                return $this->ruleBasedCompress($text);
            }
        }

        return $this->ruleBasedCompress($text);
    }

    /**
     * AI-powered compression (intelligent, preserves meaning)
     */
    protected function aiCompress(string $text): string
    {
        $provider = config('ai-guard.optimization.compression_provider', 'openai');
        $model = config('ai-guard.optimization.compression_model', 'gpt-4o-mini');

        // OpenAI compression
        if ($provider === 'openai' && class_exists(OpenAI::class)) {
            $response = OpenAI::chat()->create([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a prompt compression expert. Your job is to rewrite user prompts to be maximally concise while preserving 100% of the original meaning, intent, and key information. Rules:
- Remove filler words, greetings, politeness markers
- Keep all technical terms, numbers, names, specific requirements
- Maintain the original tone (question/command/request)
- Output ONLY the compressed prompt, no explanations
- If the prompt is already optimal, return it unchanged',
                    ],
                    [
                        'role' => 'user',
                        'content' => $text,
                    ],
                ],
                'max_tokens' => (int) ceil($this->costCalculator->estimateTokens($text) * 0.8),
                'temperature' => 0.3,
            ]);

            return trim($response->choices[0]->message->content);
        }

        // Anthropic compression
        if ($provider === 'anthropic' && class_exists(Anthropic::class)) {
            $response = Anthropic::messages()->create([
                'model' => $model,
                'max_tokens' => (int) ceil($this->costCalculator->estimateTokens($text) * 0.8),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Compress this prompt by removing filler words while keeping ALL key information:\n\n{$text}\n\nCompressed version:",
                    ],
                ],
            ]);

            return trim($response->content[0]->text);
        }

        // If no provider available, fallback
        throw new \Exception('No AI compression provider available');
    }

    /**
     * Rule-based compression (fallback, works offline)
     *
     * @var string
     */
    protected function ruleBasedCompress(string $text): string
    {
        // Step 1: Clean and normalize
        $text = preg_replace('/\s+/u', ' ', trim($text));

        if (mb_strlen($text, 'UTF-8') < 100) {
            return $text; // Too short to compress
        }

        // Step 2: Remove greetings at start
        $text = preg_replace('/^(hi|hello|hey|salut|hola|hallo|bonjour|buenos días|guten tag|ciao)[,\s!]+/iu', '', $text);

        // Step 3: Remove politeness/thanks at end
        $text = preg_replace('/\s+(thank you|thanks|merci|gracias|danke|grazie)[^.!?]*[.!?]?$/iu', '.', $text);
        $text = preg_replace('/\s+(i appreciate|i\'m grateful|i know you\'re busy)[^.!?]*[.!?]?$/iu', '.', $text);

        // Step 4: Split into sentences
        $sentences = $this->splitIntoSentences($text);

        if (count($sentences) <= 2) {
            return $this->removeFillerPhrases($text);
        }

        // Step 5: Extract keywords (TF-IDF-like approach)
        $keywords = $this->extractKeywordsFromText($text);

        // Step 6: Score sentences
        $scored = $this->scoreSentencesForCompression($sentences, $keywords);

        // Step 7: Keep most important sentences (60-70%)
        $keepCount = max(2, (int) ceil(count($scored) * 0.65));
        $topSentences = array_slice($scored, 0, $keepCount);

        // Step 8: Restore original order
        usort($topSentences, fn ($a, $b) => $a['index'] <=> $b['index']);

        // Step 9: Reconstruct and clean
        $compressed = implode(' ', array_column($topSentences, 'sentence'));
        $compressed = $this->removeFillerPhrases($compressed);

        // Step 10: Final cleanup
        $compressed = preg_replace('/\s+([,.!?])/u', '$1', $compressed);
        $compressed = preg_replace('/\s+/u', ' ', $compressed);

        return trim($compressed);
    }

    /**
     * Split text into sentences (multilingual)
     *
     * @var string
     */
    protected function splitIntoSentences(string $text): array
    {
        // Sentence boundaries for major languages
        $pattern = '/(?<=[.!?。！？।۔।])\s+/u';
        $sentences = preg_split($pattern, $text, -1, PREG_SPLIT_NO_EMPTY);

        return array_map('trim', array_filter($sentences));
    }

    /**
     * Extract important keywords using frequency and length
     *
     * @var string
     */
    protected function extractKeywordsFromText(string $text): array
    {
        // Tokenize (works across scripts)
        $words = preg_split('/[\s\p{P}]+/u', mb_strtolower($text, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY);

        // Count frequencies
        $freq = array_count_values($words);

        // Common stop words (expand as needed)
        $stopWords = [
            'the',
            'a',
            'an',
            'and',
            'or',
            'but',
            'in',
            'on',
            'at',
            'to',
            'for',
            'of',
            'with',
            'by',
            'from',
            'as',
            'is',
            'was',
            'are',
            'were',
            'be',
            'this',
            'that',
            'these',
            'those',
            'i',
            'you',
            'he',
            'she',
            'it',
            'we',
            'they',
            // French
            'le',
            'la',
            'les',
            'un',
            'une',
            'des',
            'de',
            'et',
            'ou',
            'dans',
            'sur',
            // Spanish
            'el',
            'la',
            'los',
            'las',
            'un',
            'una',
            'y',
            'o',
            'en',
            'con',
            // German
            'der',
            'die',
            'das',
            'den',
            'dem',
            'ein',
            'eine',
            'und',
            'oder',
            'in',
        ];

        // Filter: remove short words and stop words
        $keywords = [];
        foreach ($freq as $word => $count) {
            $len = mb_strlen($word, 'UTF-8');

            // Keep if: appears 2+ times OR is long (8+ chars) OR medium (4-7 chars) with high freq
            if (! in_array($word, $stopWords)) {
                if ($count >= 2 || $len >= 8 || ($len >= 4 && $count >= 2)) {
                    $keywords[$word] = $count * ($len / 10); // Weight by frequency * length
                }
            }
        }

        // Sort by importance
        arsort($keywords);

        // Return top 20 keywords
        return array_slice($keywords, 0, 20, true);
    }

    /**
     * Score sentences based on multiple factors
     *
     * @var array
     * @var array
     */
    protected function scoreSentencesForCompression(array $sentences, array $keywords): array
    {
        $scored = [];
        $totalSentences = count($sentences);

        foreach ($sentences as $index => $sentence) {
            $sLower = mb_strtolower($sentence, 'UTF-8');
            $sLength = mb_strlen($sentence, 'UTF-8');

            // Factor 1: Keyword density
            $keywordScore = 0;
            foreach ($keywords as $keyword => $weight) {
                if (mb_stripos($sLower, $keyword) !== false) {
                    $keywordScore += $weight;
                }
            }

            // Factor 2: Position importance
            $positionScore = 0;
            if ($index === 0) {
                $positionScore = 3; // First sentence very important
            } elseif ($index === $totalSentences - 1) {
                $positionScore = 1.5; // Last sentence somewhat important
            }

            // Factor 3: Length (not too short, not too long)
            $lengthScore = 0;
            if ($sLength >= 30 && $sLength <= 200) {
                $lengthScore = 1.5; // Optimal length
            } elseif ($sLength >= 20 && $sLength < 30) {
                $lengthScore = 0.8; // Short but ok
            } elseif ($sLength > 200) {
                $lengthScore = -0.5; // Too long, likely rambling
            }

            // Factor 4: Question sentences (often important)
            $questionScore = 0;
            if (preg_match('/[?？]/u', $sentence)) {
                $questionScore = 1;
            }

            // Factor 5: Contains numbers/technical terms (likely important)
            $technicalScore = 0;
            if (preg_match('/\d+|code|example|api|function|method|class|database/iu', $sentence)) {
                $technicalScore = 1.5;
            }

            $totalScore = $keywordScore + $positionScore + $lengthScore + $questionScore + $technicalScore;

            $scored[] = [
                'index' => $index,
                'sentence' => $sentence,
                'score' => $totalScore,
            ];
        }

        // Sort by score descending
        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $scored;
    }

    /**
     * Remove common filler phrases (multilingual)
     *
     * @var string
     */
    protected function removeFillerPhrases(string $text): string
    {
        $patterns = [
            // Hedging/uncertainty
            '/\b(I think|I believe|I guess|I suppose|maybe|perhaps|possibly|probably)\b\s*/iu',
            '/\b(kind of|sort of|a bit|a little|somewhat)\b\s*/iu',

            // Politeness markers
            '/\b(please|kindly|if you (could|would|can|don\'t mind))\b\s*/iu',
            '/\b(I would (like|appreciate|be grateful))\b\s*/iu',
            '/\b(could you please|would you mind)\b\s*/iu',

            // Redundant phrases
            '/\b(I was wondering if|I wanted to ask|let me know)\b\s*/iu',
            '/\b(as I mentioned|as I said|like I said)\b\s*/iu',
            '/\b(you know|you see|right)\b[,\s]*/iu',

            // Meta-commentary
            '/\b(I know this (is|might be)|this might be)\b[^.!?]*[,\s]*/iu',
            '/\b(sorry if|excuse me|pardon me)\b[^.!?]*[,\s]*/iu',

            // Filler transitions
            '/\b(so basically|essentially|actually|literally)\b\s*/iu',
            '/\b(by the way|speaking of|on that note)\b[,\s]*/iu',

            // Verbose constructions
            '/\bin order to\b/iu' => 'to',
            '/\bdue to the fact that\b/iu' => 'because',
            '/\bat this point in time\b/iu' => 'now',
            '/\bin the event that\b/iu' => 'if',
        ];

        foreach ($patterns as $pattern => $replacement) {
            if (is_int($pattern)) {
                $text = preg_replace($replacement, '', $text);
            } else {
                $text = preg_replace($pattern, $replacement, $text);
            }
        }

        return $text;
    }
}
