<?php

namespace App\Services\Ai;

use Anthropic\Beta\Messages\BetaMessage;
use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\RateLimitException;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Thin wrapper around the Anthropic SDK so the rest of the app deals in plain
 * strings / output models and a single AiException type.
 */
class AiAssistant
{
    public function __construct(
        private ?Client $client,
        private string $model,
    ) {}

    public function isConfigured(): bool
    {
        return $this->client !== null;
    }

    /**
     * Ask Claude for a response matching the given output model's schema.
     *
     * @template T of StructuredOutputModel
     *
     * @param  list<array<string, mixed>>  $content  user message content blocks
     * @param  class-string<T>  $format
     * @return T
     */
    public function structured(string $system, array $content, string $format, string $effort = 'medium'): StructuredOutputModel
    {
        $message = $this->send($system, $content, ['format' => $format, 'effort' => $effort]);

        $parsed = $message->parsedOutput();

        if (! $parsed instanceof $format) {
            throw new AiException('Claude returned a response in an unexpected format. Please try again.');
        }

        return $parsed;
    }

    /** @param  list<array<string, mixed>>  $content  user message content blocks */
    public function text(string $system, array $content, string $effort = 'medium'): string
    {
        $message = $this->send($system, $content, ['effort' => $effort]);

        $text = '';
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }

        return trim($text);
    }

    /** @param  array<string, mixed>  $outputConfig */
    private function send(string $system, array $content, array $outputConfig): BetaMessage
    {
        if (! $this->client) {
            throw new AiException('AI features are not configured. Set ANTHROPIC_API_KEY in your .env file.');
        }

        // Claude calls can take longer than PHP's default web request limit.
        set_time_limit(180);

        try {
            $message = $this->client->beta->messages->create(
                maxTokens: 16000,
                messages: [['role' => 'user', 'content' => $content]],
                model: $this->model,
                system: $system,
                outputConfig: $outputConfig,
                // If the model declines on policy grounds, let the API retry on its default fallback model.
                fallbacks: 'default',
                betas: ['server-side-fallback-2026-07-01'],
            );
        } catch (AuthenticationException $e) {
            throw new AiException('The Anthropic API key is invalid. Check ANTHROPIC_API_KEY in your .env file.', previous: $e);
        } catch (RateLimitException $e) {
            throw new AiException('Claude is busy right now. Please try again in a minute.', previous: $e);
        } catch (APIStatusException $e) {
            report($e);
            throw new AiException('Claude returned an error. Please try again.', previous: $e);
        } catch (APIConnectionException $e) {
            report($e);
            throw new AiException('Could not reach Claude. Check your internet connection and try again.', previous: $e);
        }

        if ($message->stopReason === 'refusal') {
            throw new AiException('Claude declined to answer this request.');
        }

        if ($message->stopReason === 'max_tokens') {
            throw new AiException('Claude\'s response was cut off. Please try again with less data.');
        }

        return $message;
    }
}
