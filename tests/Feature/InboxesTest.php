<?php

use Illuminate\Support\Facades\Http;
use Polkachu\LaravelAgentmail\DTOs\Inbox;
use Polkachu\LaravelAgentmail\DTOs\InboxCollection;
use Polkachu\LaravelAgentmail\Exceptions\AgentmailException;
use Polkachu\LaravelAgentmail\Facades\Agentmail;

function fakeInbox(array $overrides = []): array
{
    return array_merge([
        'pod_id' => 'pod_123',
        'inbox_id' => 'inbox_abc',
        'display_name' => 'Support <support@agentmail.to>',
        'client_id' => 'client_xyz',
        'created_at' => '2024-01-01T00:00:00Z',
        'updated_at' => '2024-01-02T00:00:00Z',
    ], $overrides);
}

it('can list inboxes', function () {
    Http::fake([
        '*/v0/inboxes*' => Http::response([
            'count' => 2,
            'inboxes' => [fakeInbox(['inbox_id' => 'inbox_1']), fakeInbox(['inbox_id' => 'inbox_2'])],
            'limit' => 50,
            'next_page_token' => null,
        ]),
    ]);

    $result = Agentmail::inboxes()->list();

    expect($result)->toBeInstanceOf(InboxCollection::class)
        ->and($result->count)->toBe(2)
        ->and($result->inboxes)->toHaveCount(2)
        ->and($result->inboxes->first())->toBeInstanceOf(Inbox::class)
        ->and($result->inboxes->first()->inboxId)->toBe('inbox_1');
});

it('passes limit and page_token when listing inboxes', function () {
    Http::fake([
        '*/v0/inboxes*' => Http::response([
            'count' => 1,
            'inboxes' => [fakeInbox()],
        ]),
    ]);

    Agentmail::inboxes()->list(limit: 10, pageToken: 'tok_abc');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'limit=10')
            && str_contains($request->url(), 'page_token=tok_abc');
    });
});

it('can create an inbox', function () {
    Http::fake([
        '*/v0/inboxes' => Http::response(fakeInbox()),
    ]);

    $inbox = Agentmail::inboxes()->create(
        username: 'support',
        domain: 'agentmail.to',
        displayName: 'Support Team',
        clientId: 'client_xyz',
    );

    expect($inbox)->toBeInstanceOf(Inbox::class)
        ->and($inbox->inboxId)->toBe('inbox_abc')
        ->and($inbox->podId)->toBe('pod_123');

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/v0/inboxes')
            && $request->data()['username'] === 'support'
            && $request->data()['display_name'] === 'Support Team';
    });
});

it('can get an inbox', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc' => Http::response(fakeInbox()),
    ]);

    $inbox = Agentmail::inboxes()->get('inbox_abc');

    expect($inbox)->toBeInstanceOf(Inbox::class)
        ->and($inbox->inboxId)->toBe('inbox_abc');

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), '/v0/inboxes/inbox_abc');
    });
});

it('can update an inbox', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc' => Http::response(fakeInbox(['display_name' => 'New Name'])),
    ]);

    $inbox = Agentmail::inboxes()->update('inbox_abc', 'New Name');

    expect($inbox)->toBeInstanceOf(Inbox::class)
        ->and($inbox->displayName)->toBe('New Name');

    Http::assertSent(function ($request) {
        return $request->method() === 'PATCH'
            && str_contains($request->url(), '/v0/inboxes/inbox_abc')
            && $request->data()['display_name'] === 'New Name';
    });
});

it('can delete an inbox', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc' => Http::response(null, 200),
    ]);

    $result = Agentmail::inboxes()->delete('inbox_abc');

    expect($result)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && str_contains($request->url(), '/v0/inboxes/inbox_abc');
    });
});

it('throws AgentmailException on 404 response', function () {
    Http::fake([
        '*/v0/inboxes/*' => Http::response([
            'name' => 'NotFoundError',
            'message' => 'Inbox not found.',
        ], 404),
    ]);

    Agentmail::inboxes()->get('missing_inbox');
})->throws(AgentmailException::class, 'Inbox not found.');

it('throws AgentmailException on 400 response', function () {
    Http::fake([
        '*/v0/inboxes' => Http::response([
            'name' => 'ValidationError',
            'message' => 'Invalid username.',
        ], 400),
    ]);

    Agentmail::inboxes()->create(username: 'invalid username!');
})->throws(AgentmailException::class, 'Invalid username.');

it('maps Inbox DTO fields correctly', function () {
    Http::fake([
        '*/v0/inboxes/inbox_abc' => Http::response(fakeInbox()),
    ]);

    $inbox = Agentmail::inboxes()->get('inbox_abc');

    expect($inbox->podId)->toBe('pod_123')
        ->and($inbox->inboxId)->toBe('inbox_abc')
        ->and($inbox->displayName)->toBe('Support <support@agentmail.to>')
        ->and($inbox->clientId)->toBe('client_xyz')
        ->and($inbox->createdAt->toDateString())->toBe('2024-01-01')
        ->and($inbox->updatedAt->toDateString())->toBe('2024-01-02');
});
