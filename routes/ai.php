<?php

declare(strict_types=1);

use App\Mcp\Servers\InstanceServer;
use Laravel\Mcp\Facades\Mcp;

/*
 * The instance administration, as an MCP server, so an assistant can run the
 * panel instead of a person clicking through it.
 *
 * It is the same gate the panel itself uses: an API key identifies the user,
 * and a user who is not an instance administrator is answered 404 rather than
 * told the server exists. Every tool goes through the same actions the screens
 * do, so the rules, the logging and the cache purging are not written twice.
 */
Mcp::web('mcp/instance', InstanceServer::class)
    ->middleware(['auth:sanctum', 'instance.admin', 'throttle:60,1'])
    ->name('mcp.instance');
