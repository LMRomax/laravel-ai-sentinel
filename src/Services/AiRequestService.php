<?php

namespace Lmromax\LaravelAiGuard\Services;

use Anthropic\Laravel\Facades\Anthropic;
use Groq\Laravel\Facades\Groq;
use Illuminate\Support\Str;
use Lmromax\LaravelAiGuard\Facades\AiGuard;

class AiRequestService
{
    /**
     * Main universal request handler
     *
     * @param  string  $model  The model name (e.g. 'gpt-4o')
     * @param  string  $prompt  The input prompt to send to the model
     * @param  array  $options  Additional options to pass to the provider's API
     * @return array An array containing 'content', 'usage', and 'raw' response data
     */
    public function request(string $model, string $prompt, array $options = []): array
    {
        $provider = $this->detectProvider($model);

        return match ($provider) {
            'openai' => $this->openai($model, $prompt, $options),
            'anthropic' => $this->anthropic($model, $prompt, $options),
            'groq' => $this->groq($model, $prompt, $options),
            'google' => $this->google($model, $prompt, $options),
            'mistral' => $this->mistral($model, $prompt, $options),
            'deepseek' => $this->deepseek($model, $prompt, $options),
            'xai' => $this->xai($model, $prompt, $options),
            default => throw new \Exception("Unknown provider for model: $model"),
        };
    }

    /**
     * Auto-detect provider using model prefixes
     *
     * @param  string  $model  The model name to detect provider for
     * @return string The detected provider name (e.g. 'openai', 'anthropic', 'groq', 'google', 'mistral', 'deepseek', 'xai')
     */
    protected function detectProvider(string $model): string
    {
        $model = strtolower($model);

        return match (true) {
            Str::startsWith($model, ['gpt-', 'o1', 'o3']) => 'openai',
            Str::contains($model, 'claude') => 'anthropic',
            Str::startsWith($model, ['llama', 'mixtral', 'gemma']) => 'groq',
            Str::startsWith($model, 'gemini') => 'google',
            Str::startsWith($model, 'mistral') => 'mistral',
            Str::startsWith($model, 'deepseek') => 'deepseek',
            Str::startsWith($model, 'grok') => 'xai',
            default => 'openai',
        };
    }

    /**
     * Optimize + Track wrapper
     *
     * This method handles the full lifecycle of an AI request, including:
     * - Prompt optimization using AiGuard::optimize()
     * - Sending the request to the appropriate provider method
     * - Tracking the request and response using AiGuard::track()
     *
     * @param  string  $provider  The provider name (e.g. 'openai')
     * @param  string  $model  The model name (e.g. 'gpt-4o')
     * @param  string  $prompt  The original prompt to send
     * @param  callable  $fn  The function to execute the provider request
     * @param  array  $options  Additional options to pass to the provider's API
     * @return array The response from the provider method
     */
    protected function process(string $provider, string $model, string $prompt, callable $fn, array $options): array
    {
        $start = microtime(true);

        // Optimize
        $optimized = AiGuard::optimize($prompt);
        $finalPrompt = $optimized['optimized'];

        // Provider request
        $response = $fn($model, $finalPrompt, $options);

        // Compute duration
        $duration = (int) ((microtime(true) - $start) * 1000);

        // Track
        AiGuard::track([
            'provider' => $provider,
            'model' => $model,
            'prompt' => $finalPrompt,
            'response' => $response['content'],
            'tokens_input' => $response['usage']['input'],
            'tokens_output' => $response['usage']['output'],
            'duration_ms' => $duration,
            'user_id' => auth()->id(),
            'metadata' => [
                'tokens_saved' => $optimized['tokens_saved'],
            ],
        ]);

        return $response;
    }

    /**
     * Provider-specific request methods
     *
     * Each method sends a request to the corresponding provider's API and returns a standardized response array containing:
     * - 'content': The generated text content from the model
     * - 'usage': An array with 'input' and 'output' token counts
     * - 'raw': The raw response object from the provider's SDK for debugging purposes
     *
     * @param  string  $model  The model name to use for the request
     * @param  string  $prompt  The prompt to send to the model
     * @param  array  $options  Additional options to pass to the provider's API
     * @return array The standardized response array from the provider method
     */
    public function openai(string $model, string $prompt, array $options = [])
    {
        return $this->process('openai', $model, $prompt, function ($model, $finalPrompt, $options) {
            $raw = \OpenAI\Laravel\Facades\OpenAI::chat()->create(array_merge([
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $finalPrompt]],
            ], $options));

            return [
                'content' => $raw->choices[0]->message->content,
                'usage' => [
                    'input' => $raw->usage->promptTokens,
                    'output' => $raw->usage->completionTokens,
                ],
                'raw' => $raw,
            ];
        }, $options);
    }

    /**
     * Other provider methods (anthropic, groq, google, mistral, deepseek, xai) are defined similarly to the openai method, each using their respective SDKs and response structures.
     * For brevity, they are not repeated here but follow the same pattern of calling $fn with the appropriate parameters and returning a standardized response array.
     *
     * @see openai() for the structure and implementation details of each provider method.
     *
     * @param  string  $model  The model name to use for the request
     * @param  string  $prompt  The prompt to send to the model
     * @param  array  $options  Additional options to pass to the provider's API
     * @return array The standardized response array from the provider method
     */
    public function anthropic(string $model, string $prompt, array $options = [])
    {
        return $this->process('anthropic', $model, $prompt, function ($model, $finalPrompt, $options) {
            $raw = Anthropic::messages()->create(array_merge([
                'model' => $model,
                'max_tokens' => 1024,
                'messages' => [['role' => 'user', 'content' => $finalPrompt]],
            ], $options));

            return [
                'content' => $raw->content[0]->text,
                'usage' => [
                    'input' => $raw->usage->inputTokens,
                    'output' => $raw->usage->outputTokens,
                ],
                'raw' => $raw,
            ];
        }, $options);
    }

    /**
     * The groq, google, mistral, deepseek, and xai methods are implemented similarly to the openai and anthropic methods, each tailored to the specific SDK and response format of the provider.
     * Each method uses the process() wrapper to handle optimization, tracking, and standardized response formatting.
     *
     * @see openai() and anthropic() for examples of how each provider method is structured and implemented.
     *
     * @param  string  $model  The model name to use for the request
     * @param  string  $prompt  The prompt to send to the model
     * @param  array  $options  Additional options to pass to the provider's API
     * @return array The standardized response array from the provider method
     */
    public function groq(string $model, string $prompt, array $options = [])
    {
        return $this->process('groq', $model, $prompt, function ($model, $finalPrompt, $options) {
            $raw = Groq::chat()->create(array_merge([
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $finalPrompt]],
            ], $options));

            return [
                'content' => $raw->choices[0]->message->content,
                'usage' => [
                    'input' => $raw->usage->prompt_tokens,
                    'output' => $raw->usage->completion_tokens,
                ],
                'raw' => $raw,
            ];
        }, $options);
    }

    /**
     * The groq, google, mistral, deepseek, and xai methods are implemented similarly to the openai and anthropic methods, each tailored to the specific SDK and response format of the provider.
     * Each method uses the process() wrapper to handle optimization, tracking, and standardized response formatting.
     *
     * @see openai() and anthropic() for examples of how each provider method is structured and implemented.
     *
     * @param  string  $model  The model name to use for the request
     * @param  string  $prompt  The prompt to send to the model
     * @param  array  $options  Additional options to pass to the provider's API
     * @return array The standardized response array from the provider method
     */
    public function google(string $model, string $prompt, array $options = [])
    {
        return $this->process('google', $model, $prompt, function ($model, $finalPrompt, $options) {
            $raw = \Gemini::client()->models->generateContent($model, $finalPrompt, $options);

            return [
                'content' => $raw->text(),
                'usage' => [
                    'input' => $raw->usage->promptTokenCount,
                    'output' => $raw->usage->candidatesTokenCount,
                ],
                'raw' => $raw,
            ];
        }, $options);
    }

    /**
     * The groq, google, mistral, deepseek, and xai methods are implemented similarly to the openai and anthropic methods, each tailored to the specific SDK and response format of the provider.
     * Each method uses the process() wrapper to handle optimization, tracking, and standardized response formatting.
     *
     * @see openai() and anthropic() for examples of how each provider method is structured and implemented.
     *
     * @param  string  $model  The model name to use for the request
     * @param  string  $prompt  The prompt to send to the model
     * @param  array  $options  Additional options to pass to the provider's API
     * @return array The standardized response array from the provider method
     */
    public function mistral(string $model, string $prompt, array $options = [])
    {
        return $this->process('mistral', $model, $prompt, function ($model, $finalPrompt, $options) {
            $raw = \Mistral::chat()->complete(array_merge([
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $finalPrompt]],
            ], $options));

            return [
                'content' => $raw->output_text,
                'usage' => [
                    'input' => $raw->usage->input_tokens,
                    'output' => $raw->usage->output_tokens,
                ],
                'raw' => $raw,
            ];
        }, $options);
    }

    /**
     * The groq, google, mistral, deepseek, and xai methods are implemented similarly to the openai and anthropic methods, each tailored to the specific SDK and response format of the provider.
     * Each method uses the process() wrapper to handle optimization, tracking, and standardized response formatting.
     *
     * @see openai() and anthropic() for examples of how each provider method is structured and implemented.
     *
     * @param  string  $model  The model name to use for the request
     * @param  string  $prompt  The prompt to send to the model
     * @param  array  $options  Additional options to pass to the provider's API
     * @return array The standardized response array from the provider method
     */
    public function deepseek(string $model, string $prompt, array $options = [])
    {
        return $this->process('deepseek', $model, $prompt, function ($model, $finalPrompt, $options) {
            $raw = \DeepSeek::chat()->create(array_merge([
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $finalPrompt]],
            ], $options));

            return [
                'content' => $raw->choices[0]->message->content,
                'usage' => [
                    'input' => $raw->usage->prompt_tokens,
                    'output' => $raw->usage->completion_tokens,
                ],
                'raw' => $raw,
            ];
        }, $options);
    }

    /**
     * The groq, google, mistral, deepseek, and xai methods are implemented similarly to the openai and anthropic methods, each tailored to the specific SDK and response format of the provider.
     * Each method uses the process() wrapper to handle optimization, tracking, and standardized response formatting.
     *
     * @see openai() and anthropic() for examples of how each provider method is structured and implemented.
     *
     * @param  string  $model  The model name to use for the request
     * @param  string  $prompt  The prompt to send to the model
     * @param  array  $options  Additional options to pass to the provider's API
     * @return array The standardized response array from the provider method
     */
    public function xai(string $model, string $prompt, array $options = [])
    {
        return $this->process('xai', $model, $prompt, function ($model, $finalPrompt, $options) {
            $raw = \XAI::messages()->create(array_merge([
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $finalPrompt]],
            ], $options));

            return [
                'content' => $raw->choices[0]->message->content,
                'usage' => [
                    'input' => $raw->usage->prompt_tokens,
                    'output' => $raw->usage->completion_tokens,
                ],
                'raw' => $raw,
            ];
        }, $options);
    }
}
