# Changelog

All notable changes to `northbees/ivendi-api` will be documented in this file.

## Unreleased

- Initial release: JSON transport over Laravel's HTTP client with retries and `{data, errors}` envelope handling, typed DTOs, and resources for quote config, quotes, payment search and the retailer's representative example.
- Per-instance credentials, base URL and quotee via `Ivendi::withCredentials()`, `withBaseUrl()` and `withQuotee()`.
- `Ivendi::fake()` and `IvendiResponse` testing helpers.
