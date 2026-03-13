---
sidebar_position: 2
title: Messages
---

# Messages

All message operations are accessed via `Agentmail::messages($inboxId)`. Each method returns a typed DTO.

```php
use Polkachu\LaravelAgentmail\Facades\Agentmail;
```

---

## List messages

Returns a `MessageCollection` containing a Laravel `Collection` of `Message` objects.

```php
$collection = Agentmail::messages('inbox_abc')->list();

foreach ($collection->messages as $message) {
    echo $message->messageId;
    echo $message->subject;
}
```

### Pagination

Pass `limit` to control page size and `pageToken` to advance through pages:

```php
$collection = Agentmail::messages('inbox_abc')->list(limit: 20);

if ($collection->nextPageToken) {
    $nextPage = Agentmail::messages('inbox_abc')->list(limit: 20, pageToken: $collection->nextPageToken);
}
```

### Filtering

```php
$collection = Agentmail::messages('inbox_abc')->list(
    labels: ['INBOX'],
    after: '2024-01-01T00:00:00Z',
    before: '2024-12-31T23:59:59Z',
    ascending: true,
    includeSpam: false,
);
```

### `MessageCollection` properties

| Property | Type | Description |
| --- | --- | --- |
| `$messages` | `Collection<Message>` | The messages on the current page |
| `$count` | `int` | Number of messages returned |
| `$limit` | `?int` | Page size limit used |
| `$nextPageToken` | `?string` | Cursor for the next page, or `null` on the last page |

---

## Get a message

```php
$message = Agentmail::messages('inbox_abc')->get('msg_001');

echo $message->messageId;
echo $message->subject;
echo $message->from;
echo $message->timestamp->toDateTimeString();
```

---

## Send a message

All parameters are optional. Pass `to`, `subject`, and `text`/`html` as needed.

```php
$response = Agentmail::messages('inbox_abc')->send(
    to: 'recipient@example.com',
    cc: 'cc@example.com',
    subject: 'Hello from AgentMail',
    text: 'This is the plain text body.',
    html: '<p>This is the HTML body.</p>',
);

echo $response->messageId;
echo $response->threadId;
```

---

## Update a message

Add or remove labels on an existing message.

```php
$message = Agentmail::messages('inbox_abc')->update(
    messageId: 'msg_001',
    addLabels: ['STARRED'],
    removeLabels: ['UNREAD'],
);
```

---

## Reply to a message

```php
$response = Agentmail::messages('inbox_abc')->reply(
    messageId: 'msg_001',
    text: 'Thanks for reaching out!',
);

echo $response->messageId;
```

---

## Reply all to a message

```php
$response = Agentmail::messages('inbox_abc')->replyAll(
    messageId: 'msg_001',
    text: 'Replying to everyone.',
);
```

---

## Forward a message

```php
$response = Agentmail::messages('inbox_abc')->forward(
    messageId: 'msg_001',
    to: 'other@example.com',
    text: 'FYI — forwarding this along.',
);
```

---

## The `Message` DTO

| Property | Type | Description |
| --- | --- | --- |
| `$inboxId` | `string` | The inbox this message belongs to |
| `$threadId` | `string` | Thread identifier |
| `$messageId` | `string` | Unique message identifier |
| `$labels` | `array` | Array of label strings (e.g. `['INBOX', 'UNREAD']`) |
| `$timestamp` | `Carbon` | When the message was sent/received |
| `$from` | `string` | Sender address |
| `$to` | `array` | Recipient addresses |
| `$size` | `int` | Message size in bytes |
| `$updatedAt` | `Carbon` | Last updated timestamp |
| `$createdAt` | `Carbon` | Creation timestamp |
| `$cc` | `?array` | CC addresses |
| `$bcc` | `?array` | BCC addresses |
| `$replyTo` | `?array` | Reply-to addresses |
| `$subject` | `?string` | Email subject |
| `$preview` | `?string` | Short preview text |
| `$text` | `?string` | Plain text body |
| `$html` | `?string` | HTML body |
| `$extractedText` | `?string` | Extracted plain text |
| `$extractedHtml` | `?string` | Extracted HTML |
| `$inReplyTo` | `?string` | Message ID this is a reply to |
| `$references` | `?array` | Reference message IDs |
| `$attachments` | `?Collection<Attachment>` | Attachments |
| `$headers` | `?array` | Custom headers |

---

## The `Attachment` DTO

| Property | Type | Description |
| --- | --- | --- |
| `$attachmentId` | `string` | Unique attachment identifier |
| `$size` | `int` | Attachment size in bytes |
| `$filename` | `?string` | Original filename |
| `$contentType` | `?string` | MIME content type |
| `$contentDisposition` | `?string` | Content disposition header value |
| `$contentId` | `?string` | Content ID for inline attachments |

---

## The `SendMessageResponse` DTO

Returned by `send()`, `reply()`, `replyAll()`, and `forward()`:

| Property | Type | Description |
| --- | --- | --- |
| `$messageId` | `string` | ID of the newly created message |
| `$threadId` | `string` | Thread the message belongs to |

---

## Testing

Use Laravel's `Http::fake()` to mock AgentMail responses without making real API calls:

```php
use Illuminate\Support\Facades\Http;
use Polkachu\LaravelAgentmail\Facades\Agentmail;

Http::fake([
    '*/v0/inboxes/inbox_abc/messages*' => Http::response([
        'count'    => 1,
        'messages' => [[
            'inbox_id'   => 'inbox_abc',
            'thread_id'  => 'thread_xyz',
            'message_id' => 'msg_001',
            'labels'     => ['INBOX'],
            'timestamp'  => '2024-01-01T10:00:00Z',
            'from'       => 'sender@example.com',
            'to'         => ['recipient@example.com'],
            'size'       => 1024,
            'updated_at' => '2024-01-02T00:00:00Z',
            'created_at' => '2024-01-01T00:00:00Z',
        ]],
    ]),
]);

$collection = Agentmail::messages('inbox_abc')->list();

expect($collection->count)->toBe(1);
expect($collection->messages->first()->messageId)->toBe('msg_001');
```
