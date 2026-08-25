<?php

declare(strict_types=1);

use App\Services\BlogPostRenderer;

it('renders markdown to html', function () {
    $rendered = app(BlogPostRenderer::class)->convert("Opening.\n\n## A heading\n\nMore text.");

    expect($rendered['html'])->toContain('<p>Opening.</p>')
        ->and($rendered['html'])->toContain('More text.');
});

it('gives every heading an id and collects the contents list', function () {
    $rendered = app(BlogPostRenderer::class)->convert("## First\n\ntext\n\n### Nested\n\ntext");

    expect($rendered['html'])->toContain('id="first"')
        ->and($rendered['html'])->toContain('id="nested"')
        ->and($rendered['toc'])->toBe([
            ['id' => 'first', 'text' => 'First', 'level' => 2],
            ['id' => 'nested', 'text' => 'Nested', 'level' => 3],
        ]);
});

it('numbers a repeated heading rather than reusing its id', function () {
    $rendered = app(BlogPostRenderer::class)->convert("## Notes\n\na\n\n## Notes\n\nb");

    expect($rendered['toc'][0]['id'])->toBe('notes')
        ->and($rendered['toc'][1]['id'])->toBe('notes-2');
});

it('keeps footnotes joined to their reference', function () {
    $rendered = app(BlogPostRenderer::class)->convert("A claim.[^1]\n\n[^1]: The source.");

    expect($rendered['html'])->toContain('id="fnref:1"')
        ->and($rendered['html'])->toContain('id="fn:1"')
        ->and($rendered['html'])->toContain('The source.');
});

it('strips a script tag out of the body, leaving its contents inert text', function () {
    $rendered = app(BlogPostRenderer::class)->convert('Text <script>alert(1)</script> more.');

    expect($rendered['html'])->not->toContain('<script')
        ->and($rendered['html'])->not->toContain('</script>');
});

it('strips an event handler off an element', function () {
    $rendered = app(BlogPostRenderer::class)->convert('<p onclick="alert(1)">Text</p>');

    expect($rendered['html'])->not->toContain('onclick');
});

it('refuses an unsafe link scheme', function () {
    $rendered = app(BlogPostRenderer::class)->convert('[click](javascript:alert(1))');

    expect($rendered['html'])->not->toContain('javascript:');
});
