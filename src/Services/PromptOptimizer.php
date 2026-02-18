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
     */
    protected function compress(string $text): string
    {
        if (! config('ai-guard.optimization.enable_compression', true)) {
            return $text;
        }

        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove common filler phrases
        $fillers = [
            'Please provide' => 'Provide',
            'Can you help me' => 'Help me',
            'I would like to' => 'I want to',
            'Could you please' => 'Please',
            'I am wondering if' => 'Can',
            'It would be great if' => 'Please',
        ];

        $text = str_replace(array_keys($fillers), array_values($fillers), $text);

        // Trim
        return trim($text);
    }

    /**
     * Truncate context to fit within token limit
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

        return substr($context, 0, $targetLength).'...';
    }
}
