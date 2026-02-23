<?php

namespace Lmromax\LaravelAiGuard\Services;

use Lmromax\LaravelAiGuard\Facades\AiGuard;
use OpenAI\Laravel\Facades\OpenAI;

class AiRequestService
{
    /**
     * Make an OpenAI request with automatic tracking
     */
    public function openai(string $model, string $prompt, array $options = []): array
    {
        $startTime = microtime(true);

        // Optimize prompt
        $optimized = AiGuard::optimize($prompt);
        $finalPrompt = $optimized['optimized'];

        // Make OpenAI call
        $response = OpenAI::chat()->create(array_merge([
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $finalPrompt],
            ],
        ], $options));

        $duration = (int) ((microtime(true) - $startTime) * 1000);

        // Track
        AiGuard::track([
            'provider' => 'openai',
            'model' => $model,
            'prompt' => $finalPrompt,
            'response' => $response->choices[0]->message->content,
            'tokens_input' => $response->usage->promptTokens,
            'tokens_output' => $response->usage->completionTokens,
            'duration_ms' => $duration,
            'user_id' => auth()->id(),
            'metadata' => [
                'tokens_saved' => $optimized['tokens_saved'],
            ],
        ]);

        return [
            'content' => $response->choices[0]->message->content,
            'usage' => [
                'input_tokens' => $response->usage->promptTokens,
                'output_tokens' => $response->usage->completionTokens,
            ],
            'raw' => $response,
        ];
    }

    /**
     * Make an Anthropic request with automatic tracking
     */
    public function anthropic(string $model, string $prompt, array $options = []): array
    {
        $startTime = microtime(true);

        // Optimize prompt
        $optimized = AiGuard::optimize($prompt);
        $finalPrompt = $optimized['optimized'];

        // Make Anthropic call
        $anthropic = app(\Anthropic\Laravel\Facades\Anthropic::class);
        $response = $anthropic->messages()->create(array_merge([
            'model' => $model,
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $finalPrompt],
            ],
        ], $options));

        $duration = (int) ((microtime(true) - $startTime) * 1000);

        // Track
        AiGuard::track([
            'provider' => 'anthropic',
            'model' => $model,
            'prompt' => $finalPrompt,
            'response' => $response->content[0]->text,
            'tokens_input' => $response->usage->inputTokens,
            'tokens_output' => $response->usage->outputTokens,
            'duration_ms' => $duration,
            'user_id' => auth()->id(),
            'metadata' => [
                'tokens_saved' => $optimized['tokens_saved'],
            ],
        ]);

        return [
            'content' => $response->content[0]->text,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
            ],
            'raw' => $response,
        ];
    }
}
