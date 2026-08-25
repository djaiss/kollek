<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function initializeCall(): array
{
    return [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-06-18',
            'capabilities' => [],
            'clientInfo' => ['name' => 'the-office', 'version' => '1.0.0'],
        ],
    ];
}

it('refuses an unauthenticated client', function () {
    $response = $this->postJson('mcp/instance', initializeCall());

    $response->assertUnauthorized();
});

it('hides the server from a user who does not administer the instance', function () {
    Sanctum::actingAs($this->createUser());

    $response = $this->postJson('mcp/instance', initializeCall());

    $response->assertNotFound();
});

it('answers an instance administrator', function () {
    Sanctum::actingAs($this->createUser(['is_instance_administrator' => true]));

    $response = $this->postJson('mcp/instance', initializeCall());

    $response->assertOk();
    $response->assertSee('Kollek instance administration');
});

it('offers the blog tools', function () {
    Sanctum::actingAs($this->createUser(['is_instance_administrator' => true]));

    $response = $this->postJson('mcp/instance', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/list',
    ]);

    $response->assertOk();
    $response->assertSee(['list-blog-posts', 'create-blog-post', 'write-blog-post-translation']);
    $response->assertDontSee('destroy-blog-post');
});
