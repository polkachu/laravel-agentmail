# Plan: Implement AgentMail Messages Endpoints

## Context

Phase 1 of this project added full CRUD support for AgentMail's **Inboxes** API. This phase adds support for the **Messages** API (`/v0/inboxes/{inbox_id}/messages`), following the exact same architecture: TDD with Pest, readonly DTOs, a Resource class, Facade access, and Docusaurus docs.

---

## Operations to Implement

| Method | Endpoint | Description |
|--------|----------|-------------|
| `list()` | GET `/v0/inboxes/{inbox_id}/messages` | Paginated message list with filters |
| `get()` | GET `/v0/inboxes/{inbox_id}/messages/{message_id}` | Single full message |
| `send()` | POST `/v0/inboxes/{inbox_id}/messages` | Send a new email |
| `update()` | PATCH `/v0/inboxes/{inbox_id}/messages/{message_id}` | Add/remove labels |
| `reply()` | POST `/v0/inboxes/{inbox_id}/messages/{message_id}/reply` | Reply to a message |
| `replyAll()` | POST `/v0/inboxes/{inbox_id}/messages/{message_id}/reply-all` | Reply all |
| `forward()` | POST `/v0/inboxes/{inbox_id}/messages/{message_id}/forward` | Forward a message |

---

## New Files to Create

### DTOs

**`src/DTOs/Attachment.php`** — Readonly DTO for attachment metadata
- Required: `attachmentId` (string), `size` (int)
- Optional: `filename` (?string), `contentType` (?string), `contentDisposition` (?string), `contentId` (?string)
- Static `fromArray(array $data): self` factory

**`src/DTOs/Message.php`** — Readonly DTO for a full message
- Required: `inboxId`, `threadId`, `messageId` (strings), `labels` (array), `timestamp` (Carbon), `from` (string), `to` (array), `size` (int), `updatedAt` (Carbon), `createdAt` (Carbon)
- Optional: `cc`, `bcc`, `replyTo` (arrays), `subject`, `preview`, `text`, `html`, `extractedText`, `extractedHtml`, `inReplyTo` (strings), `references` (array), `attachments` (Collection of Attachment), `headers` (array)
- Static `fromArray(array $data): self` factory; parse timestamps with Carbon; map attachments to `Attachment::fromArray()`

**`src/DTOs/MessageCollection.php`** — Readonly paginated wrapper
- Properties: `messages` (Collection of Message), `count` (int), `limit` (?int), `nextPageToken` (?string)
- Mirrors the pattern of `InboxCollection`

**`src/DTOs/SendMessageResponse.php`** — Readonly response for send/reply/forward
- Properties: `messageId` (string), `threadId` (string)
- Static `fromArray(array $data): self` factory

### Resource

**`src/Resources/Messages.php`**
```php
class Messages
{
    public function __construct(
        private readonly PendingRequest $http,
        private readonly string $inboxId,
    ) {}
```
- `list()` — accepts optional: `?int $limit`, `?string $pageToken`, `?array $labels`, `?string $before`, `?string $after`, `?bool $ascending`, `?bool $includeSpam`, `?bool $includeBlocked`, `?bool $includeTrash` → returns `MessageCollection`
- `get(string $messageId)` → returns `Message`
- `send()` — accepts optional: `string|array|null $to`, `string|array|null $cc`, `string|array|null $bcc`, `?string $subject`, `?string $text`, `?string $html`, `string|array|null $replyTo`, `?array $labels`, `?array $headers` → returns `SendMessageResponse`
- `update(string $messageId, ?array $addLabels, ?array $removeLabels)` → returns `Message`
- `reply(string $messageId, ...)` — same optional params as send minus subject, plus `?bool $replyAll` → returns `SendMessageResponse`
- `replyAll(string $messageId, ...)` — same as reply, calls `/reply-all` → returns `SendMessageResponse`
- `forward(string $messageId, ...)` — same optional params as send → returns `SendMessageResponse`
- All methods: filter null params with `array_filter`, throw `AgentmailException::fromResponse()` on failure

### Tests

**`tests/Feature/MessagesTest.php`** — TDD, written before implementation
- Helper `fakeMessage(array $overrides = []): array` with all required fields
- Helper `fakeSendResponse(): array` returning `['message_id' => ..., 'thread_id' => ...]`
- Tests (follow same pattern as `InboxesTest.php`):
  - `it('can list messages')` — asserts `MessageCollection`, count, first message's `messageId`
  - `it('passes query params when listing messages')` — verifies limit/page_token in URL
  - `it('can get a message')` — asserts `Message` DTO and field mapping
  - `it('can send a message')` — asserts `SendMessageResponse`, verifies POST body
  - `it('can update a message')` — asserts `Message`, verifies PATCH + body `add_labels`/`remove_labels`
  - `it('can reply to a message')` — asserts `SendMessageResponse`, verifies POST URL
  - `it('can reply all to a message')` — verifies `/reply-all` URL
  - `it('can forward a message')` — verifies `/forward` URL
  - `it('throws AgentmailException on error')` — 404 case
  - `it('maps Message DTO fields correctly')` — all fields including Carbon timestamps and attachments

### Documentation

**`docusaurus/docs/usage/messages.md`** — mirrors `inboxes.md` format with:
- Usage examples for all 7 operations
- Pagination section for `list()`
- `MessageCollection`, `Message`, `Attachment`, `SendMessageResponse` property tables
- `Http::fake()` testing example

---

## Files to Modify

**`src/Agentmail.php`**
- Add `use Polkachu\LaravelAgentmail\Resources\Messages;`
- Add method: `public function messages(string $inboxId): Messages`

**`docusaurus/openapi/openapi.yaml`**
- Add paths for all 7 message operations under `/v0/inboxes/{inbox_id}/messages`
- Add schemas: `Message`, `MessageCollection`, `Attachment`, `SendMessageRequest`, `SendMessageResponse`, `UpdateMessageRequest`

**`docusaurus/sidebars.js`**
- Add `'usage/messages'` to the Usage category items (before `'usage/error-handling'`)

---

## TDD Order of Execution

1. Write `tests/Feature/MessagesTest.php` (all tests fail — red)
2. Create `src/DTOs/Attachment.php`
3. Create `src/DTOs/Message.php`
4. Create `src/DTOs/MessageCollection.php`
5. Create `src/DTOs/SendMessageResponse.php`
6. Create `src/Resources/Messages.php`
7. Update `src/Agentmail.php`
8. Run `composer test` — all tests should pass (green)
9. Update `docusaurus/openapi/openapi.yaml`
10. Create `docusaurus/docs/usage/messages.md`
11. Update `docusaurus/sidebars.js`

---

## Verification

- Run `composer test` — all tests pass (existing `InboxesTest` + new `MessagesTest`)
- Verify `Agentmail::messages('inbox_abc')->list()` returns `MessageCollection`
- Verify `Agentmail::messages('inbox_abc')->send(to: 'test@example.com', subject: 'Hi')` returns `SendMessageResponse`
- Check Docusaurus site: `cd docusaurus && npm start`, navigate to Usage → Messages
