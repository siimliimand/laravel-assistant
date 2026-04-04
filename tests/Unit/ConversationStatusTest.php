<?php

use App\Enums\ConversationStatus;

test('conversation status has correct values', function () {
    expect(ConversationStatus::Active->value)->toBe('active');
    expect(ConversationStatus::Archived->value)->toBe('archived');
    expect(ConversationStatus::Deleted->value)->toBe('deleted');
});

test('conversation status returns correct labels', function () {
    expect(ConversationStatus::Active->label())->toBe('Active');
    expect(ConversationStatus::Archived->label())->toBe('Archived');
    expect(ConversationStatus::Deleted->label())->toBe('Deleted');
});

test('conversation status returns correct colors', function () {
    expect(ConversationStatus::Active->color())->toBe('green');
    expect(ConversationStatus::Archived->color())->toBe('gray');
    expect(ConversationStatus::Deleted->color())->toBe('red');
});

test('conversation status returns correct icons', function () {
    expect(ConversationStatus::Active->icon())->toBe('chat');
    expect(ConversationStatus::Archived->icon())->toBe('archive');
    expect(ConversationStatus::Deleted->icon())->toBe('trash');
});

test('conversation status isActive returns correct boolean', function () {
    expect(ConversationStatus::Active->isActive())->toBeTrue();
    expect(ConversationStatus::Archived->isActive())->toBeFalse();
    expect(ConversationStatus::Deleted->isActive())->toBeFalse();
});

test('conversation status isArchived returns correct boolean', function () {
    expect(ConversationStatus::Active->isArchived())->toBeFalse();
    expect(ConversationStatus::Archived->isArchived())->toBeTrue();
    expect(ConversationStatus::Deleted->isArchived())->toBeFalse();
});

test('conversation status isDeleted returns correct boolean', function () {
    expect(ConversationStatus::Active->isDeleted())->toBeFalse();
    expect(ConversationStatus::Archived->isDeleted())->toBeFalse();
    expect(ConversationStatus::Deleted->isDeleted())->toBeTrue();
});

test('conversation status can be instantiated from backed value', function () {
    expect(ConversationStatus::from('active'))->toBe(ConversationStatus::Active);
    expect(ConversationStatus::from('archived'))->toBe(ConversationStatus::Archived);
    expect(ConversationStatus::from('deleted'))->toBe(ConversationStatus::Deleted);
});

test('conversation status tryFrom returns null for invalid value', function () {
    expect(ConversationStatus::tryFrom('invalid'))->toBeNull();
    expect(ConversationStatus::tryFrom('active'))->toBe(ConversationStatus::Active);
});
