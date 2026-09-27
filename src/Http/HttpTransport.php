<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Http;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use NorthBees\IvendiApi\Data\IvendiError;
use NorthBees\IvendiApi\Enums\Endpoint;
use NorthBees\IvendiApi\Exceptions\IvendiAuthenticationException;
use NorthBees\IvendiApi\Exceptions\IvendiConnectionException;
use NorthBees\IvendiApi\Exceptions\IvendiInvalidResponseException;
use NorthBees\IvendiApi\Exceptions\IvendiRequestException;
use NorthBees\IvendiApi\Exceptions\IvendiValidationException;
use Throwable;

/**
 * Sends JSON requests through Laravel's HTTP client and unwraps iVendi's
 * `{data, errors}` envelope.
 */
final class HttpTransport
{
    /**
     * @param  array<string, mixed>  $config  the resolved `ivendi` config
     */
    public function __construct(private readonly array $config) {}

    /**
     * @param  array<array-key, mixed>|null  $payload
     * @return array<string, mixed> the envelope's `data`
     *
     * @throws IvendiConnectionException|IvendiAuthenticationException|IvendiValidationException|IvendiRequestException|IvendiInvalidResponseException
     */
    public function send(Endpoint $endpoint, string $baseUrl, #[\SensitiveParameter] string $apiKey, ?array $payload = null, string ...$pathParameters): array
    {
        $startedAt = microtime(true);
        $response = $this->request($endpoint, $baseUrl, $apiKey, $payload, $pathParameters);

        $this->log($endpoint, $response->status(), $startedAt);

        return $this->parse($endpoint, $response);
    }

    /**
     * @param  array<array-key, mixed>|null  $payload
     * @param  array<int, string>  $pathParameters
     */
    private function request(Endpoint $endpoint, string $baseUrl, string $apiKey, ?array $payload, array $pathParameters): Response
    {
        $url = rtrim($baseUrl, '/').$endpoint->path(...$pathParameters);

        try {
            $request = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept-Language' => (string) data_get($this->config, 'accept_language', 'en-GB'),
            ])
                ->acceptJson()
                ->timeout((int) data_get($this->config, 'timeout', 15))
                ->connectTimeout((int) data_get($this->config, 'connect_timeout', 5))
                ->retry(
                    max(1, (int) data_get($this->config, 'retry.times', 2) + 1),
                    (int) data_get($this->config, 'retry.sleep_ms', 250),
                    fn (Throwable $exception): bool => $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && in_array($exception->response->status(), [429, 502, 503, 504], true)),
                    throw: false,
                );

            return $endpoint->method() === 'GET'
                ? $request->get($url)
                : $request->asJson()->post($url, $payload ?? []);
        } catch (ConnectionException $exception) {
            throw new IvendiConnectionException("Unable to connect to iVendi ({$endpoint->value}).", $endpoint, previous: $exception);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function parse(Endpoint $endpoint, Response $response): array
    {
        if ($response->serverError()) {
            throw new IvendiConnectionException("iVendi {$endpoint->value} returned HTTP {$response->status()}.", $endpoint, $response->status());
        }

        $body = $response->json();
        $errors = is_array($body) ? IvendiError::collect($body['errors'] ?? $body['error'] ?? null) : collect();

        if ($response->clientError()) {
            $message = "iVendi {$endpoint->value} returned HTTP {$response->status()}".($errors->isNotEmpty() ? ': '.$errors->pluck('message')->implode('; ') : '.');

            throw match (true) {
                in_array($response->status(), [401, 403], true) => new IvendiAuthenticationException($message, $endpoint, $errors, $response->status()),
                $response->status() === 400 => new IvendiValidationException($message, $endpoint, $errors, $response->status()),
                default => new IvendiRequestException($message, $endpoint, $errors, $response->status()),
            };
        }

        if (! is_array($body)) {
            throw new IvendiInvalidResponseException("iVendi {$endpoint->value} returned a response that is not JSON.", $endpoint);
        }

        $data = $body['data'] ?? null;

        if (! is_array($data)) {
            if ($errors->isNotEmpty()) {
                throw new IvendiRequestException("iVendi {$endpoint->value} failed: ".$errors->pluck('message')->implode('; '), $endpoint, $errors, $response->status());
            }

            throw new IvendiInvalidResponseException("iVendi {$endpoint->value} response had no data.", $endpoint);
        }

        return $data;
    }

    private function log(Endpoint $endpoint, int $status, float $startedAt): void
    {
        if (! data_get($this->config, 'logging.enabled', false)) {
            return;
        }

        Log::channel(data_get($this->config, 'logging.channel'))->info('iVendi request', [
            'endpoint' => $endpoint->value,
            'status' => $status,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);
    }
}
