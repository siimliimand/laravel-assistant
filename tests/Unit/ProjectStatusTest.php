<?php

use App\Enums\ProjectStatus;

test('project status has correct values', function () {
    expect(ProjectStatus::Draft->value)->toBe('draft');
    expect(ProjectStatus::Active->value)->toBe('active');
    expect(ProjectStatus::Completed->value)->toBe('completed');
    expect(ProjectStatus::Archived->value)->toBe('archived');
});

test('project status returns correct labels', function () {
    expect(ProjectStatus::Draft->label())->toBe('Draft');
    expect(ProjectStatus::Active->label())->toBe('Active');
    expect(ProjectStatus::Completed->label())->toBe('Completed');
    expect(ProjectStatus::Archived->label())->toBe('Archived');
});

test('project status returns correct colors', function () {
    expect(ProjectStatus::Draft->color())->toBe('gray');
    expect(ProjectStatus::Active->color())->toBe('blue');
    expect(ProjectStatus::Completed->color())->toBe('green');
    expect(ProjectStatus::Archived->color())->toBe('purple');
});

test('project status returns correct icons', function () {
    expect(ProjectStatus::Draft->icon())->toBe('document');
    expect(ProjectStatus::Active->icon())->toBe('spark');
    expect(ProjectStatus::Completed->icon())->toBe('check');
    expect(ProjectStatus::Archived->icon())->toBe('archive');
});

test('project status isDraft returns correct boolean', function () {
    expect(ProjectStatus::Draft->isDraft())->toBeTrue();
    expect(ProjectStatus::Active->isDraft())->toBeFalse();
    expect(ProjectStatus::Completed->isDraft())->toBeFalse();
    expect(ProjectStatus::Archived->isDraft())->toBeFalse();
});

test('project status isActive returns correct boolean', function () {
    expect(ProjectStatus::Draft->isActive())->toBeFalse();
    expect(ProjectStatus::Active->isActive())->toBeTrue();
    expect(ProjectStatus::Completed->isActive())->toBeFalse();
    expect(ProjectStatus::Archived->isActive())->toBeFalse();
});

test('project status isCompleted returns correct boolean', function () {
    expect(ProjectStatus::Draft->isCompleted())->toBeFalse();
    expect(ProjectStatus::Active->isCompleted())->toBeFalse();
    expect(ProjectStatus::Completed->isCompleted())->toBeTrue();
    expect(ProjectStatus::Archived->isCompleted())->toBeFalse();
});

test('project status isArchived returns correct boolean', function () {
    expect(ProjectStatus::Draft->isArchived())->toBeFalse();
    expect(ProjectStatus::Active->isArchived())->toBeFalse();
    expect(ProjectStatus::Completed->isArchived())->toBeFalse();
    expect(ProjectStatus::Archived->isArchived())->toBeTrue();
});

test('project status can be instantiated from backed value', function () {
    expect(ProjectStatus::from('draft'))->toBe(ProjectStatus::Draft);
    expect(ProjectStatus::from('active'))->toBe(ProjectStatus::Active);
    expect(ProjectStatus::from('completed'))->toBe(ProjectStatus::Completed);
    expect(ProjectStatus::from('archived'))->toBe(ProjectStatus::Archived);
});

test('project status tryFrom returns null for invalid value', function () {
    expect(ProjectStatus::tryFrom('invalid'))->toBeNull();
    expect(ProjectStatus::tryFrom('draft'))->toBe(ProjectStatus::Draft);
    expect(ProjectStatus::tryFrom('active'))->toBe(ProjectStatus::Active);
});
