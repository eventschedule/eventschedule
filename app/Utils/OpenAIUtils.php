<?php

namespace App\Utils;

class OpenAIUtils
{
    /**
     * Send a text prompt to OpenAI GPT-4o and return parsed JSON response.
     * Matches the return format of GeminiUtils::sendRequest().
     *
     * @return array|null Parsed response data or null on failure
     */
    public static function sendTextRequest($prompt, $imageData = null, $disableThinking = false, $purpose = 'content')
    {
        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            return null;
        }

        $messages = [];
        $content = [];

        // Add image data if provided
        if ($imageData) {
            $detectedFormat = ImageUtils::detectImageFormat($imageData, 'temp_image');
            $mimeType = ImageUtils::getImageMimeType($detectedFormat);
            $base64 = base64_encode($imageData);

            $content[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => "data:{$mimeType};base64,{$base64}",
                ],
            ];
        }

        // Add text prompt
        if ($prompt) {
            $content[] = [
                'type' => 'text',
                'text' => $prompt,
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $content];

        $data = [
            'model' => config('services.openai.'.($purpose === 'translation' ? 'translation_model' : 'content_model'), 'gpt-4o'),
            'messages' => $messages,
            'response_format' => ['type' => 'json_object'],
        ];

        if (config('app.is_testing')) {
            \Log::info('AI request', ['provider' => 'openai', 'model' => $data['model'], 'type' => 'text', 'purpose' => $purpose]);
        }

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 55,
        ]);

        $response = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErrno === CURLE_OPERATION_TIMEDOUT) {
            $exception = new \Exception('OpenAI API text request timed out');

            \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($exception, $prompt): void {
                $scope->setLevel(\Sentry\Severity::warning());
                $scope->setTag('service', 'openai');
                $scope->setTag('error_type', 'timeout');
                $scope->setContext('openai_api', [
                    'prompt_preview' => substr($prompt, 0, 100),
                ]);
                \Sentry\captureException($exception);
            });

            return null;
        }

        if ($httpCode !== 200) {
            \Log::error('OpenAI API error response: '.$response);

            $errorData = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($errorData['error']['message'])) {
                $errorMessage = $errorData['error']['message'];

                if (str_contains($errorMessage, 'quota') || $httpCode === 429) {
                    $exception = new \Exception('OpenAI API quota/rate limit exceeded: '.$errorMessage);

                    \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($exception, $httpCode, $response, $prompt): void {
                        $scope->setLevel(\Sentry\Severity::error());
                        $scope->setTag('service', 'openai');
                        $scope->setTag('error_type', 'quota_exceeded');
                        $scope->setContext('openai_api', [
                            'http_code' => $httpCode,
                            'response' => $response,
                            'prompt_preview' => substr($prompt, 0, 100),
                        ]);
                        \Sentry\captureException($exception);
                    });

                    return null;
                }

                if ($httpCode === 503 || str_contains(strtolower($errorMessage), 'overloaded')) {
                    $exception = new \Exception('OpenAI API model overloaded: '.$errorMessage);

                    \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($exception, $httpCode, $response, $prompt): void {
                        $scope->setLevel(\Sentry\Severity::warning());
                        $scope->setTag('service', 'openai');
                        $scope->setTag('error_type', 'model_overloaded');
                        $scope->setContext('openai_api', [
                            'http_code' => $httpCode,
                            'response' => $response,
                            'prompt_preview' => substr($prompt, 0, 100),
                        ]);
                        \Sentry\captureException($exception);
                    });

                    return null;
                }

                throw new \Exception($errorMessage);
            }

            throw new \Exception('OpenAI API request failed with status code: '.$httpCode);
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('Failed to parse OpenAI API response: '.$response);
            throw new \Exception('Invalid JSON response from OpenAI API');
        }

        $text = $data['choices'][0]['message']['content'] ?? null;
        if (! $text) {
            \Log::error('Unexpected OpenAI API response structure: '.json_encode($data));
            throw new \Exception('Unexpected response structure from OpenAI API');
        }

        $parsed = json_decode($text, true);

        // Handle both array and object response formats (match GeminiUtils behavior)
        if (is_array($parsed) && isset($parsed[0])) {
            return $parsed;
        }

        return [$parsed];
    }

    /**
     * Send a single synchronous OpenAI image generation request.
     *
     * @return string|null Decoded image bytes or null on failure
     */
    public static function sendImageGenerationRequest(string $prompt, string $aspectRatio = '3:4'): ?string
    {
        $imageProvider = config('services.ai.image_provider', 'openai');

        if ($imageProvider === 'gemini' && config('services.google.gemini_key')) {
            return GeminiUtils::sendImageGenerationRequest($prompt, $aspectRatio);
        }

        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            if (config('services.google.gemini_key')) {
                return GeminiUtils::sendImageGenerationRequest($prompt, $aspectRatio);
            }
            throw new \Exception('No AI image generation API key is configured');
        }

        $model = config('services.openai.image_model', 'gpt-image-1.5');
        $size = self::mapAspectRatioToSize($aspectRatio, $model);

        if (config('app.is_testing')) {
            \Log::info('AI request', ['provider' => 'openai', 'model' => $model, 'type' => 'image']);
        }

        $data = self::buildImageRequestPayload($model, $prompt, $size);

        $ch = curl_init('https://api.openai.com/v1/images/generations');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 120,
        ]);

        $response = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErrno === CURLE_OPERATION_TIMEDOUT) {
            $exception = new \Exception('OpenAI API request timed out');

            \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($exception, $prompt): void {
                $scope->setLevel(\Sentry\Severity::warning());
                $scope->setTag('service', 'openai');
                $scope->setTag('error_type', 'timeout');
                $scope->setContext('openai_api', [
                    'prompt_preview' => substr($prompt, 0, 100),
                ]);
                \Sentry\captureException($exception);
            });

            return null;
        }

        return self::processImageGenerationResponse($response, $httpCode, $prompt);
    }

    /**
     * Prepare an OpenAI image generation request for parallel execution.
     *
     * @return array [$curlHandle, $prompt]
     */
    public static function prepareImageGenerationRequest(string $prompt, string $aspectRatio = '3:4'): array
    {
        $imageProvider = config('services.ai.image_provider', 'openai');

        if ($imageProvider === 'gemini' && config('services.google.gemini_key')) {
            return GeminiUtils::prepareImageGenerationRequest($prompt, $aspectRatio);
        }

        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            if (config('services.google.gemini_key')) {
                return GeminiUtils::prepareImageGenerationRequest($prompt, $aspectRatio);
            }
            throw new \Exception('No AI image generation API key is configured');
        }

        $model = config('services.openai.image_model', 'gpt-image-1.5');
        $size = self::mapAspectRatioToSize($aspectRatio, $model);

        if (config('app.is_testing')) {
            \Log::info('AI request', ['provider' => 'openai', 'model' => $model, 'type' => 'image']);
        }

        $data = self::buildImageRequestPayload($model, $prompt, $size);

        $ch = curl_init('https://api.openai.com/v1/images/generations');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 120,
        ]);

        return [$ch, $prompt];
    }

    /**
     * Build the request payload for OpenAI image generation, adapting to the model.
     */
    private static function buildImageRequestPayload(string $model, string $prompt, string $size): array
    {
        if (self::isGptImageModel($model)) {
            return [
                'model' => $model,
                'prompt' => $prompt,
                'size' => $size,
                'quality' => 'high',
                'output_format' => 'png',
            ];
        }

        // DALL-E 3 fallback
        return [
            'model' => $model,
            'prompt' => $prompt,
            'n' => 1,
            'size' => $size,
            'quality' => 'hd',
            'response_format' => 'b64_json',
        ];
    }

    /**
     * Parse OpenAI image generation response and extract image data.
     *
     * @return string|null Decoded image bytes or null on failure
     */
    public static function processImageGenerationResponse(string $response, int $httpCode, string $prompt): ?string
    {
        if ($httpCode !== 200) {
            \Log::error('OpenAI image generation API error response: '.$response);

            $errorData = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($errorData['error'])) {
                $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
                $errorCode = $errorData['error']['code'] ?? null;

                // Content policy violation or moderation block
                if ($errorCode === 'content_policy_violation' || $errorCode === 'moderation_blocked') {
                    \Log::warning('OpenAI image filtered by content policy: '.$errorMessage);

                    throw new \App\Exceptions\ContentModerationException($errorMessage);
                }

                // Rate limit
                if ($httpCode === 429) {
                    $exception = new \Exception('OpenAI API rate limit exceeded: '.$errorMessage);

                    \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($exception, $httpCode, $response, $prompt): void {
                        $scope->setLevel(\Sentry\Severity::error());
                        $scope->setTag('service', 'openai');
                        $scope->setTag('error_type', 'rate_limit');
                        $scope->setContext('openai_api', [
                            'http_code' => $httpCode,
                            'response' => $response,
                            'prompt_preview' => substr($prompt, 0, 100),
                        ]);
                        \Sentry\captureException($exception);
                    });

                    return null;
                }

                throw new \Exception($errorMessage);
            }

            throw new \Exception('OpenAI image generation API request failed with status code: '.$httpCode);
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('Failed to parse OpenAI image generation API response: '.$response);
            throw new \Exception('Invalid JSON response from OpenAI image generation API');
        }

        $imageBase64 = $data['data'][0]['b64_json'] ?? null;
        if ($imageBase64) {
            return base64_decode($imageBase64);
        }

        \Log::error('No image data in OpenAI response: '.json_encode($data));

        return null;
    }

    /**
     * Execute multiple OpenAI image requests in parallel using curl_multi.
     */
    public static function executeParallelImageRequests(array $requests): array
    {
        $imageProvider = config('services.ai.image_provider', 'openai');

        if ($imageProvider === 'gemini' && config('services.google.gemini_key')) {
            return GeminiUtils::executeParallelImageRequests($requests);
        }

        if (! config('services.openai.api_key') && config('services.google.gemini_key')) {
            return GeminiUtils::executeParallelImageRequests($requests);
        }

        $mh = curl_multi_init();
        $handles = [];

        foreach ($requests as $key => [$ch, $prompt]) {
            curl_multi_add_handle($mh, $ch);
            $handles[$key] = ['ch' => $ch, 'prompt' => $prompt];
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
            if ($running > 0) {
                curl_multi_select($mh);
            }
        } while ($running > 0);

        $results = [];
        foreach ($handles as $key => ['ch' => $ch, 'prompt' => $prompt]) {
            $response = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);

            try {
                $results[$key] = self::processImageGenerationResponse($response, $httpCode, $prompt);
            } catch (\Exception $e) {
                \Log::error('OpenAI parallel image generation failed for '.$key.': '.$e->getMessage());
                $results[$key] = null;
            }
        }

        curl_multi_close($mh);

        return $results;
    }

    /**
     * Check if the model is a GPT Image family model.
     */
    private static function isGptImageModel(string $model): bool
    {
        return str_starts_with($model, 'gpt-image');
    }

    /**
     * Map aspect ratio strings to supported image sizes for the given model.
     */
    private static function mapAspectRatioToSize(string $aspectRatio, string $model): string
    {
        if (self::isGptImageModel($model)) {
            return match ($aspectRatio) {
                '1:1' => '1024x1024',
                '3:4' => '1024x1536',
                '16:9' => '1536x1024',
                default => '1024x1536',
            };
        }

        // DALL-E 3 sizes
        return match ($aspectRatio) {
            '1:1' => '1024x1024',
            '3:4' => '1024x1792',
            '16:9' => '1792x1024',
            default => '1024x1792',
        };
    }
}
