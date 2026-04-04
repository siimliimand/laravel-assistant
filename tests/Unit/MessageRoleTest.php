<?php

use App\Enums\MessageRole;

test('message role has correct values', function () {
    expect(MessageRole::User->value)->toBe('user');
    expect(MessageRole::Assistant->value)->toBe('assistant');
});

test('message role returns correct labels', function () {
    expect(MessageRole::User->label())->toBe('You');
    expect(MessageRole::Assistant->label())->toBe('DevBot');
});

test('message role returns correct colors', function () {
    expect(MessageRole::User->color())->toBe('blue');
    expect(MessageRole::Assistant->color())->toBe('green');
});

test('message role returns correct icons', function () {
    expect(MessageRole::User->icon())->toBe('user');
    expect(MessageRole::Assistant->icon())->toBe('robot');
});

test('message role isUser returns correct boolean', function () {
    expect(MessageRole::User->isUser())->toBeTrue();
    expect(MessageRole::Assistant->isUser())->toBeFalse();
});

test('message role isAssistant returns correct boolean', function () {
    expect(MessageRole::User->isAssistant())->toBeFalse();
    expect(MessageRole::Assistant->isAssistant())->toBeTrue();
});

test('message role can be instantiated from backed value', function () {
    expect(MessageRole::from('user'))->toBe(MessageRole::User);
    expect(MessageRole::from('assistant'))->toBe(MessageRole::Assistant);
});

test('message role tryFrom returns null for invalid value', function () {
    expect(MessageRole::tryFrom('invalid'))->toBeNull();
    expect(MessageRole::tryFrom('user'))->toBe(MessageRole::User);
});
