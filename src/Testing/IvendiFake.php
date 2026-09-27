<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Testing;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use NorthBees\IvendiApi\Enums\Endpoint;
use PHPUnit\Framework\Assert;

/**
 * Fakes the iVendi Connect API via Http::fake(), routing on method and path.
 * Array responses are wrapped in the `{data, errors}` envelope.
 */
final class IvendiFake
{
    /**
     * @var list<array{key: string, url: string, payload: array<array-key, mixed>}>
     */
    private array $sent = [];

    /**
     * @param  array<string, mixed>  $responses
     */
    private function __construct(private array $responses) {}

    /**
     * @param  array<string, mixed>  $responses  keyed by endpoint name (see Endpoint) or "*"
     */
    public static function fake(array $responses = []): self
    {
        $fake = new self($responses);
        $host = parse_url((string) config('ivendi.base_url'), PHP_URL_HOST);

        Http::fake(function (Request $request) use ($fake, $host): ?PromiseInterface {
            $matches = is_string($host) && $host !== ''
                ? parse_url($request->url(), PHP_URL_HOST) === $host
                : Endpoint::fromRequest($request->method(), (string) parse_url($request->url(), PHP_URL_PATH)) !== null;

            return $matches ? $fake->respond($request) : null;
        });

        return $fake;
    }

    /**
     * @param  array<array-key, mixed>|PromiseInterface|Closure  $response
     */
    public function push(Endpoint|string $key, array|PromiseInterface|Closure $response): self
    {
        $this->responses[$key instanceof Endpoint ? $key->value : $key] = $response;

        return $this;
    }

    /**
     * @param  (Closure(array<array-key, mixed>): bool)|null  $callback  receives the request payload
     */
    public function assertSent(Endpoint|string $key, ?Closure $callback = null): void
    {
        $key = $key instanceof Endpoint ? $key->value : $key;
        $matching = array_filter($this->sent, fn (array $request): bool => $request['key'] === $key && ($callback === null || $callback($request['payload'])));

        Assert::assertNotEmpty($matching, "Expected iVendi request [{$key}] was not sent.");
    }

    public function assertSentTimes(Endpoint|string $key, int $times): void
    {
        $key = $key instanceof Endpoint ? $key->value : $key;
        $count = count(array_filter($this->sent, fn (array $request): bool => $request['key'] === $key));

        Assert::assertSame($times, $count, "Expected iVendi request [{$key}] {$times} times, sent {$count}.");
    }

    public function assertNotSent(Endpoint|string $key): void
    {
        $this->assertSentTimes($key, 0);
    }

    public function assertNothingSent(): void
    {
        Assert::assertSame([], $this->sent, 'Unexpected iVendi requests were sent.');
    }

    /**
     * @return list<array{key: string, url: string, payload: array<array-key, mixed>}>
     */
    public function sent(): array
    {
        return $this->sent;
    }

    private function respond(Request $request): PromiseInterface
    {
        $key = Endpoint::fromRequest($request->method(), (string) parse_url($request->url(), PHP_URL_PATH))->value ?? 'unknown';
        $payload = $request->method() === 'GET' ? [] : $request->data();

        $this->sent[] = ['key' => $key, 'url' => $request->url(), 'payload' => $payload];

        $response = $this->responses[$key] ?? $this->responses['*'] ?? null;

        if ($response === null) {
            return IvendiResponse::error("iVendi request [{$key}] was not faked.", 404);
        }

        if ($response instanceof Closure) {
            $response = $response($payload);
        }

        return $response instanceof PromiseInterface ? $response : Http::response(['data' => $response, 'errors' => null]);
    }
}
