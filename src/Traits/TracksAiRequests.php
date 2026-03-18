<?php

namespace Lmromax\LaravelAiSentinel\Traits;

use Lmromax\LaravelAiSentinel\Facades\AiSentinel;

trait TracksAiRequests
{
    /**
     * Make an AI request with automatic tracking and optimization
     *
     * @param  string  $provider  The AI provider name (e.g. 'openai')
     * @param  string  $model  The AI model name (e.g. 'gpt-4o')
     * @param  string  $prompt  The prompt to send to the AI
     * @param  array  $options  Additional options for the AI call (e.g. max_tokens, temperature)
     * @return array The response from the AI provider
     */
    protected function aiRequest(string $provider, string $model, string $prompt, array $options = []): array
    {
        $startTime = microtime(true);

        // Step 1: Optimize the prompt if enabled
        $optimized = AiSentinel::optimize($prompt);
        $finalPrompt = $optimized['optimized'];

        // Step 2: Make the actual AI call
        $response = $this->callAiProvider($provider, $model, $finalPrompt, $options);

        // Step 3: Calculate duration
        $duration = (int) ((microtime(true) - $startTime) * 1000);

        // Step 4: Track the request
        AiSentinel::track([
            'provider' => $provider,
            'model' => $model,
            'prompt' => $finalPrompt,
            'response' => $response['content'] ?? '',
            'tokens_input' => $response['usage']['input_tokens'] ?? 0,
            'tokens_output' => $response['usage']['output_tokens'] ?? 0,
            'duration_ms' => $duration,
            'user_id' => auth()->id(),
            'metadata' => [
                'original_prompt_tokens' => $optimized['tokens_original'],
                'tokens_saved' => $optimized['tokens_saved'],
                'optimization_enabled' => config('ai-sentinel.optimization.enabled', true),
            ],
        ]);

        return $response;
    }

    /**
     * Override this method to implement your AI provider logic
     */
    abstract protected function callAiProvider(string $provider, string $model, string $prompt, array $options): array;
}
