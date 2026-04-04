<?php

use App\Helpers\Markdown;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->conversation = Conversation::factory()->create([
        'title' => 'Test Conversation',
    ]);
});

test('markdown helper converts basic markdown to html', function () {
    $markdown = '**bold text** and *italic text*';
    $html = Markdown::convert($markdown);

    expect($html)->toContain('<strong>bold text</strong>');
    expect($html)->toContain('<em>italic text</em>');
});

test('markdown helper converts code blocks', function () {
    $markdown = "```php\necho 'Hello World';\n```";
    $html = Markdown::convert($markdown);

    expect($html)->toContain('<pre>');
    expect($html)->toContain('<code class="language-php">');
    expect($html)->toContain('echo \'Hello World\';');
});

test('markdown helper converts inline code', function () {
    $markdown = 'Use the `route()` helper';
    $html = Markdown::convert($markdown);

    expect($html)->toContain('<code>');
    expect($html)->toContain('route()');
});

test('markdown helper converts headings', function () {
    $markdown = "# Heading 1\n## Heading 2\n### Heading 3";
    $html = Markdown::convert($markdown);

    expect($html)->toContain('<h1>Heading 1</h1>');
    expect($html)->toContain('<h2>Heading 2</h2>');
    expect($html)->toContain('<h3>Heading 3</h3>');
});

test('markdown helper converts lists', function () {
    $markdown = "- Item 1\n- Item 2\n- Item 3";
    $html = Markdown::convert($markdown);

    expect($html)->toContain('<ul>');
    expect($html)->toContain('<li>Item 1</li>');
    expect($html)->toContain('<li>Item 2</li>');
    expect($html)->toContain('<li>Item 3</li>');
});

test('markdown helper converts links', function () {
    $markdown = '[Laravel Docs](https://laravel.com)';
    $html = Markdown::convert($markdown);

    expect($html)->toContain('<a href="https://laravel.com">Laravel Docs</a>');
});

test('markdown helper escapes unsafe html', function () {
    $markdown = '<script>alert("xss")</script>';
    $html = Markdown::convert($markdown);

    expect($html)->not->toContain('<script>');
    expect($html)->toContain('&lt;script&gt;');
});

test('message model formats content with markdown', function () {
    $message = Message::factory()->assistantMessage('**Bold** and `code`')
        ->create(['conversation_id' => $this->conversation->id]);

    $formatted = $message->formattedContent();

    expect($formatted)->toContain('<strong>Bold</strong>');
    expect($formatted)->toContain('<code>code</code>');
});

test('message model renders code blocks correctly', function () {
    $message = Message::factory()->assistantMessage("Here's an example:\n\n```php\nreturn 'hello';\n```")
        ->create(['conversation_id' => $this->conversation->id]);

    $formatted = $message->formattedContent();

    expect($formatted)->toContain('<pre>');
    expect($formatted)->toContain('<code class="language-php">');
    expect($formatted)->toContain("return 'hello';");
});

test('chat interface displays formatted markdown', function () {
    $message = Message::factory()->assistantMessage('**Hello** with `code`')
        ->create(['conversation_id' => $this->conversation->id]);

    $response = $this->get(route('chat.show.conversation', ['conversation' => $this->conversation->id]));

    $response->assertStatus(200);
    $response->assertSee('<strong>Hello</strong>', false);
    $response->assertSee('<code>code</code>', false);
    $response->assertSee('markdown-content');
});
