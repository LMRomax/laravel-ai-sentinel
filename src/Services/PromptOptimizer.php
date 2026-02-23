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
        // Normalisation
        $clean = preg_replace('/\s+/u', ' ', trim($text));
        $clean = preg_replace('/([.!?])\1+/u', '$1', $clean);

        // Découpage en phrases
        $sentences = preg_split('/(?<=[.!?])\s+/u', $clean, -1, PREG_SPLIT_NO_EMPTY);

        if (count($sentences) < 2) return $clean;

        // 1) Détecter les phrases d'intention (FR/EN)
        $intent = [];
        $intentRegex = '/\b(
        explain|describe|show|help|guide|detail|understand|clarify|teach|
        expliquer|décrire|montrer|aider|détailler|comprendre|clarifier
    )\b/iu';

        // 2) Détecter les phrases contenant un objet multi-mots (les idées)
        $ideas = [];
        $ideaRegex = '/\b[\p{L}\d]{3,}(?:\s+[\p{L}\d]{3,}){1,5}\b/u';

        // 3) Détecter contraintes (FR/EN)
        $constraints = [];
        $constraintsRegex = '/\b(
        step[- ]?by[- ]?step|clearly|structured|simplify|examples?|illustrations?|
        étape par étape|clairement|structuré|exemples?|schéma
    )\b/iu';

        // On retire le bruit dans chaque phrase (hedging, sorry, thanks…)
        $noisePatterns = [
            '/\b(sorry|thank|thanks|désolé|merci)\b/iu',
            '/\b(maybe|perhaps|actually|basically|you know|genre|en fait)\b/iu',
            '/\b(i was thinking|i wanted to|je voulais|je pensais)\b/iu',
        ];

        foreach ($sentences as $index => $s) {
            $sClean = trim($s);

            foreach ($noisePatterns as $pattern) {
                $sClean = preg_replace($pattern, '', $sClean);
            }
            $sClean = trim($sClean);

            // Phrase d'intention
            if (preg_match($intentRegex, $sClean)) {
                $intent[] = ['index' => $index, 'text' => $sClean];
                continue;
            }

            // Phrase contenant des objets multi-mots → idées utiles
            if (preg_match($ideaRegex, $sClean)) {
                $ideas[] = ['index' => $index, 'text' => $sClean];
            }

            // Phrase contenant des contraintes
            if (preg_match($constraintsRegex, $sClean)) {
                $constraints[] = ['index' => $index, 'text' => $sClean];
            }
        }

        // 4) Sélection finale ↓↓↓

        $final = [];

        // 1 seule phrase d'intention, la première dans le texte
        if (!empty($intent)) {
            usort($intent, fn($a, $b) => $a['index'] <=> $b['index']);
            $final[] = $intent[0]['text'];
        }

        // Ajouter 1–3 idées → les plus proches de la phrase d'intention
        if (!empty($ideas)) {
            usort($ideas, fn($a, $b) => $a['index'] <=> $b['index']);
            $final = array_merge($final, array_slice(array_column($ideas, 'text'), 0, 3));
        }

        // Ajouter 0–1 contrainte importante
        if (!empty($constraints)) {
            usort($constraints, fn($a, $b) => $a['index'] <=> $b['index']);
            $final[] = $constraints[0]['text'];
        }

        // Reconstruction
        $out = implode(' ', $final);
        $out = preg_replace('/\s+([,.!?])/', '$1', $out);
        return trim($out);
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
