<?php

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('project has user relationship', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    expect($project->user)->toBeInstanceOf(User::class);
    expect($project->user->id)->toBe($user->id);
});

test('project belongs to user', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    expect($project->user()->associate($user))->toBeInstanceOf(Project::class);
});

test('user has projects relationship', function () {
    $user = User::factory()->create();
    $projects = Project::factory()->count(3)->create(['user_id' => $user->id]);

    expect($user->projects)->toHaveCount(3);
    expect($user->projects->first())->toBeInstanceOf(Project::class);
});

test('project factory creates valid project', function () {
    $project = Project::factory()->create();

    expect($project->id)->not->toBeNull();
    expect($project->name)->toBeString();
    expect($project->description)->toBeString();
    expect($project->status)->toBeInstanceOf(ProjectStatus::class);
    expect($project->user_id)->not->toBeNull();
});

test('project factory draft state', function () {
    $project = Project::factory()->draft()->create();

    expect($project->status)->toBe(ProjectStatus::Draft);
});

test('project factory active state', function () {
    $project = Project::factory()->active()->create();

    expect($project->status)->toBe(ProjectStatus::Active);
});

test('project factory completed state', function () {
    $project = Project::factory()->completed()->create();

    expect($project->status)->toBe(ProjectStatus::Completed);
});

test('project factory archived state', function () {
    $project = Project::factory()->archived()->create();

    expect($project->status)->toBe(ProjectStatus::Archived);
});

test('project status is cast to enum', function () {
    $project = Project::factory()->create(['status' => ProjectStatus::Active]);

    expect($project->status)->toBeInstanceOf(ProjectStatus::class);
    expect($project->status)->toBe(ProjectStatus::Active);
});

test('project fillable attributes', function () {
    $project = new Project;

    expect($project->getFillable())->toContain('user_id', 'name', 'description', 'status');
});
