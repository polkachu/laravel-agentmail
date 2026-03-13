---
sidebar_position: 2
title: Error Handling
---

# Error Handling

Any non-2xx response from the AgentMail API throws an `AgentmailException`.

## Exception properties

| Member | Type | Description |
| --- | --- | --- |
| `$errorName` | `string` | Machine-readable error name from the API (e.g. `"NotFoundError"`) |
| `getMessage()` | `string` | Human-readable error description from the API |
| `getCode()` | `int` | HTTP status code (e.g. `404`, `422`) |

## Example

```php
use Polkachu\LaravelAgentmail\Exceptions\AgentmailException;
use Polkachu\LaravelAgentmail\Facades\Agentmail;

try {
    $inbox = Agentmail::inboxes()->get('inbox_does_not_exist');
} catch (AgentmailException $e) {
    echo $e->errorName;    // "NotFoundError"
    echo $e->getMessage(); // "Inbox not found."
    echo $e->getCode();    // 404
}
```

## Common error names

| Error name | HTTP status | Cause |
| --- | --- | --- |
| `UnauthorizedError` | 401 | Missing or invalid API key |
| `NotFoundError` | 404 | Inbox ID does not exist |
| `ValidationError` | 422 | Invalid request body (e.g. bad username format) |
| `InternalServerError` | 500 | Unexpected server-side error |
