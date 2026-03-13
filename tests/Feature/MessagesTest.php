<?php

use Illuminate\Support\Facades\Http;
use Polkachu\LaravelAgentmail\DTOs\Attachment;
use Polkachu\LaravelAgentmail\DTOs\Message;
use Polkachu\LaravelAgentmail\DTOs\MessageCollection;
use Polkachu\LaravelAgentmail\DTOs\SendMessageResponse;
use Polkachu\LaravelAgentmail\Exceptions\AgentmailException;
use Polkachu\LaravelAgentmail\Facades\Agentmail;

function fakeMessage(array $overrides = []): array
{
    return array_merge([
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
    ], $overrides);
}

function fakeSendResponse(): array
{
    return [
        'message_id' => 'msg_new_001',
        'thread_id'  => 'thread_new_xyz',
    ];
}

it('can list messages', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc/messages*' => Http::response([
            'count'           => 2,
            'messages'        => [fakeMessage(['message_id' => 'msg_1']), fakeMessage(['message_id' => 'msg_2'])],
            'limit'           => 50,
            'next_page_token' => null,
        ]),
    ]);

    $result = Agentmail::messages('inbox_abc')->list();

    expect($result)->toBeInstanceOf(MessageCollection::class)
        ->and($result->count)->toBe(2)
        ->and($result->messages)->toHaveCount(2)
        ->and($result->messages->first())->toBeInstanceOf(Message::class)
        ->and($result->messages->first()->messageId)->toBe('msg_1');
});

it('passes query params when listing messages', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc/messages*' => Http::response([
            'count'    => 1,
            'messages' => [fakeMessage()],
        ]),
    ]);

    Agentmail::messages('inbox_abc')->list(limit: 10, pageToken: 'tok_abc');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'limit=10')
            && str_contains($request->url(), 'page_token=tok_abc');
    });
});

it('can get a message', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc/messages/msg_001' => Http::response(fakeMessage()),
    ]);

    $message = Agentmail::messages('inbox_abc')->get('msg_001');

    expect($message)->toBeInstanceOf(Message::class)
        ->and($message->messageId)->toBe('msg_001')
        ->and($message->inboxId)->toBe('inbox_abc');

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), '/v0/inboxes/inbox_abc/messages/msg_001');
    });
});

it('can send a message', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc/messages' => Http::response(fakeSendResponse()),
    ]);

    $response = Agentmail::messages('inbox_abc')->send(
        to: 'recipient@example.com',
        subject: 'Hello',
        text: 'Hello world',
    );

    expect($response)->toBeInstanceOf(SendMessageResponse::class)
        ->and($response->messageId)->toBe('msg_new_001')
        ->and($response->threadId)->toBe('thread_new_xyz');

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/v0/inboxes/inbox_abc/messages')
            && $request->data()['to'] === 'recipient@example.com'
            && $request->data()['subject'] === 'Hello';
    });
});

it('can update a message', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc/messages/msg_001' => Http::response(fakeMessage(['labels' => ['INBOX', 'STARRED']])),
    ]);

    $message = Agentmail::messages('inbox_abc')->update('msg_001', addLabels: ['STARRED'], removeLabels: []);

    expect($message)->toBeInstanceOf(Message::class)
        ->and($message->labels)->toContain('STARRED');

    Http::assertSent(function ($request) {
        return $request->method() === 'PATCH'
            && str_contains($request->url(), '/v0/inboxes/inbox_abc/messages/msg_001')
            && $request->data()['add_labels'] === ['STARRED'];
    });
});

it('can reply to a message', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc/messages/msg_001/reply' => Http::response(fakeSendResponse()),
    ]);

    $response = Agentmail::messages('inbox_abc')->reply('msg_001', text: 'Thanks!');

    expect($response)->toBeInstanceOf(SendMessageResponse::class);

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/v0/inboxes/inbox_abc/messages/msg_001/reply');
    });
});

it('can reply all to a message', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc/messages/msg_001/reply-all' => Http::response(fakeSendResponse()),
    ]);

    $response = Agentmail::messages('inbox_abc')->replyAll('msg_001', text: 'Thanks all!');

    expect($response)->toBeInstanceOf(SendMessageResponse::class);

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/v0/inboxes/inbox_abc/messages/msg_001/reply-all');
    });
});

it('can forward a message', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc/messages/msg_001/forward' => Http::response(fakeSendResponse()),
    ]);

    $response = Agentmail::messages('inbox_abc')->forward('msg_001', to: 'other@example.com');

    expect($response)->toBeInstanceOf(SendMessageResponse::class);

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/v0/inboxes/inbox_abc/messages/msg_001/forward');
    });
});

it('throws AgentmailException on error', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc/messages/missing_msg' => Http::response([
            'name'    => 'NotFoundError',
            'message' => 'Message not found.',
        ], 404),
    ]);

    Agentmail::messages('inbox_abc')->get('missing_msg');
})->throws(AgentmailException::class, 'Message not found.');

it('maps Message DTO fields correctly', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc/messages/msg_001' => Http::response(array_merge(fakeMessage(), [
            'subject'          => 'Test Subject',
            'preview'          => 'Preview text',
            'text'             => 'Plain text body',
            'cc'               => ['cc@example.com'],
            'bcc'              => ['bcc@example.com'],
            'reply_to'         => ['replyto@example.com'],
            'in_reply_to'      => 'original_msg_id',
            'references'       => ['ref_1'],
            'attachments'      => [
                [
                    'attachment_id'       => 'att_001',
                    'filename'            => 'file.pdf',
                    'content_type'        => 'application/pdf',
                    'content_disposition' => 'attachment',
                    'content_id'          => 'cid_001',
                    'size'                => 2048,
                ],
            ],
            'headers' => ['X-Custom' => 'value'],
        ])),
    ]);

    $message = Agentmail::messages('inbox_abc')->get('msg_001');

    expect($message->inboxId)->toBe('inbox_abc')
        ->and($message->threadId)->toBe('thread_xyz')
        ->and($message->messageId)->toBe('msg_001')
        ->and($message->labels)->toBe(['INBOX'])
        ->and($message->from)->toBe('sender@example.com')
        ->and($message->to)->toBe(['recipient@example.com'])
        ->and($message->size)->toBe(1024)
        ->and($message->subject)->toBe('Test Subject')
        ->and($message->preview)->toBe('Preview text')
        ->and($message->text)->toBe('Plain text body')
        ->and($message->cc)->toBe(['cc@example.com'])
        ->and($message->bcc)->toBe(['bcc@example.com'])
        ->and($message->replyTo)->toBe(['replyto@example.com'])
        ->and($message->inReplyTo)->toBe('original_msg_id')
        ->and($message->references)->toBe(['ref_1'])
        ->and($message->timestamp->toDateString())->toBe('2024-01-01')
        ->and($message->createdAt->toDateString())->toBe('2024-01-01')
        ->and($message->updatedAt->toDateString())->toBe('2024-01-02')
        ->and($message->attachments)->toHaveCount(1)
        ->and($message->attachments->first())->toBeInstanceOf(Attachment::class)
        ->and($message->attachments->first()->attachmentId)->toBe('att_001')
        ->and($message->attachments->first()->filename)->toBe('file.pdf');
});
