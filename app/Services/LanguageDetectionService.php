<?php

namespace App\Services;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Throwable;

use function Laravel\Ai\agent;

class LanguageDetectionService
{
    public function detect(string $text): ?string
    {
        try {
            $response = agent(
                instructions: <<<'PROMPT'
                Detect the language of the provided text.

                Return the ISO 639-1 language code.

                Examples:
                French => fr
                English => en
                Arabic => ar
                Spanish => es

                Do not translate the text.
                PROMPT,
                schema: fn (JsonSchema $schema) => [
                    'language' => $schema
                        ->string()
                        ->required(),
                ],
            )->prompt(
                $text,
                provider: 'groq'
            );

            return strtolower($response['language']);
        } catch (Throwable $exception) {
            Log::warning('Language detection failed', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
