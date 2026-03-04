# Plan: laravel-agentmail — Inboxes API Integration

## Context

Building `polkachu/laravel-agentmail`, a Laravel package that wraps the AgentMail REST API. The package starts with Inbox CRUD operations only. It must follow Laravel conventions (ServiceProvider, Facade, config publishing) and use Pest PHP as the test suite.

---

## Package Architecture

```
laravel-agentmail/
├── config/
│   └── agentmail.php                  # api_key, base_url
├── src/
│   ├── AgentmailServiceProvider.php   # registers singleton, publishes config
│   ├── Facades/
│   │   └── Agentmail.php              # facade pointing to Agentmail::class
│   ├── Resources/
│   │   └── Inboxes.php                # all 5 inbox operations
│   ├── DTOs/
│   │   ├── Inbox.php                  # readonly value object
│   │   └── InboxCollection.php        # paginated list wrapper
│   ├── Exceptions/
│   │   └── AgentmailException.php     # wraps API error responses
│   └── Agentmail.php                  # facade backing class; exposes inboxes()
├── tests/
│   ├── Feature/
│   │   └── InboxesTest.php            # Pest feature tests
│   └── Pest.php                       # Pest bootstrap
└── composer.json
```

---

## Step-by-Step Implementation

### 1. `composer.json` — finalize dependencies

Add:

- `require`: `illuminate/support ^10|^11|^12`, `illuminate/http ^10|^11|^12`
- `require-dev`: `pestphp/pest ^2`, `orchestra/testbench ^8|^9|^10`, `pestphp/pest-plugin-laravel ^2`
- Autoload-dev PSR-4: `"Polkachu\\LaravelAgentmail\\Tests\\" => "tests/"`
- `extra.laravel.providers`: `Polkachu\LaravelAgentmail\AgentmailServiceProvider`

### 2. `config/agentmail.php`

```php
return [
    'api_key'  => env('AGENTMAIL_API_KEY'),
    'base_url' => env('AGENTMAIL_BASE_URL', 'https://api.agentmail.to'),
];
```

### 3. `src/Exceptions/AgentmailException.php`

```php
class AgentmailException extends RuntimeException
{
    public function __construct(
        public readonly string $errorName,
        string $message,
        int $statusCode = 0,
    ) {
        parent::__construct($message, $statusCode);
    }

    public static function fromResponse(Response $response): self { ... }
}
```

### 4. `src/DTOs/Inbox.php` — readonly value object

Fields from the API schema:

- `string $podId` (required)
- `string $inboxId` (required)
- `Carbon $updatedAt` (required)
- `Carbon $createdAt` (required)
- `?string $displayName` (optional)
- `?string $clientId` (optional)

Includes a `static fromArray(array $data): self` factory method that maps snake_case keys and parses Carbon timestamps.

### 5. `src/DTOs/InboxCollection.php`

```php
readonly class InboxCollection
{
    public function __construct(
        public Collection $inboxes,   // Collection<Inbox>
        public int $count,
        public ?int $limit,
        public ?string $nextPageToken,
    ) {}

    public static function fromArray(array $data): self { ... }
}
```

### 6. `src/Resources/Inboxes.php`

Receives a pre-configured `PendingRequest` (base URL + Bearer token). Implements:

| Method   | Signature                                                                                                            | Maps to                         |
| -------- | -------------------------------------------------------------------------------------------------------------------- | ------------------------------- |
| `list`   | `list(int $limit = null, string $pageToken = null): InboxCollection`                                                 | `GET /v0/inboxes`               |
| `create` | `create(string $username = null, string $domain = null, string $displayName = null, string $clientId = null): Inbox` | `POST /v0/inboxes`              |
| `get`    | `get(string $inboxId): Inbox`                                                                                        | `GET /v0/inboxes/{inbox_id}`    |
| `update` | `update(string $inboxId, string $displayName): Inbox`                                                                | `PATCH /v0/inboxes/{inbox_id}`  |
| `delete` | `delete(string $inboxId): bool`                                                                                      | `DELETE /v0/inboxes/{inbox_id}` |

Each method throws `AgentmailException` on non-2xx responses.

### 7. `src/Agentmail.php` — facade backing class

```php
class Agentmail
{
    public function __construct(private readonly PendingRequest $http) {}

    public function inboxes(): Inboxes
    {
        return new Inboxes($this->http);
    }
}
```

### 8. `src/AgentmailServiceProvider.php`

- Merges config from `config/agentmail.php`
- Publishes config under `agentmail` tag
- Binds `Agentmail::class` as a singleton, building the `Http::baseUrl()->withToken()->asJson()->acceptJson()` `PendingRequest`

### 9. `src/Facades/Agentmail.php`

```php
class Agentmail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Polkachu\LaravelAgentmail\Agentmail::class;
    }
}
```

### 10. `tests/Pest.php` + `tests/Feature/InboxesTest.php`

- Pest.php: `uses(Tests\TestCase::class)->in('Feature');`
- `TestCase` extends `Orchestra\Testbench\TestCase`, registers the ServiceProvider
- Tests use `Http::fake()` to mock API responses — no real HTTP calls
- Cover all 5 operations: list, create, get, update, delete
- Also test: `AgentmailException` is thrown on 404 / 400 responses

---

## Usage (after integration)

```php
// In .env:
AGENTMAIL_API_KEY=your_key_here

// List inboxes
$collection = Agentmail::inboxes()->list(limit: 10);

// Create inbox
$inbox = Agentmail::inboxes()->create(username: 'support', displayName: 'Support Team');

// Get inbox
$inbox = Agentmail::inboxes()->get('inbox_123');

// Update inbox
$inbox = Agentmail::inboxes()->update('inbox_123', 'New Name <support@agentmail.to>');

// Delete inbox
Agentmail::inboxes()->delete('inbox_123');
```

---

## Verification

1. Run `composer install` and confirm no dependency conflicts
2. Run `./vendor/bin/pest` — all tests green, no real HTTP calls made
3. Verify `Http::assertSent()` in tests confirms correct endpoint, method, and payload
4. Publish config via `php artisan vendor:publish --tag=agentmail` and confirm file appears in `config/`
