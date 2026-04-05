<?php

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

/**
 * ==========================================
 * Authentication Tests
 * ==========================================
 */
test('unauthenticated user cannot access projects index', function () {
    auth()->logout();

    $response = $this->get(route('projects.index'));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot access projects create', function () {
    auth()->logout();

    $response = $this->get(route('projects.create'));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot store project', function () {
    auth()->logout();

    $response = $this->post(route('projects.store'), [
        'name' => 'Test Project',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot view project', function () {
    auth()->logout();

    $project = Project::factory()->create();

    $response = $this->get(route('projects.show', $project));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot edit project', function () {
    auth()->logout();

    $project = Project::factory()->create();

    $response = $this->get(route('projects.edit', $project));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot update project', function () {
    auth()->logout();

    $project = Project::factory()->create();

    $response = $this->put(route('projects.update', $project), [
        'name' => 'Updated Project',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot delete project', function () {
    auth()->logout();

    $project = Project::factory()->create();

    $response = $this->delete(route('projects.destroy', $project));

    $response->assertRedirect(route('login'));
});

/**
 * ==========================================
 * Index Tests
 * ==========================================
 */
test('authenticated user can access projects index', function () {
    $response = $this->get(route('projects.index'));

    $response->assertSuccessful();
    $response->assertViewIs('projects.index');
    $response->assertViewHas('projects');
});

test('index shows only authenticated users projects', function () {
    $otherUser = User::factory()->create();

    $userProjects = Project::factory()->count(3)->create(['user_id' => $this->user->id]);
    $otherProjects = Project::factory()->count(2)->create(['user_id' => $otherUser->id]);

    $response = $this->get(route('projects.index'));

    $response->assertSuccessful();
    $projects = $response->viewData('projects');
    expect($projects->count())->toBe(3);
    expect($projects->pluck('id')->toArray())->toContain(...$userProjects->pluck('id')->toArray());
    expect($projects->pluck('id')->toArray())->not->toContain(...$otherProjects->pluck('id')->toArray());
});

test('index shows projects ordered by latest first', function () {
    $older = Project::factory()->create(['user_id' => $this->user->id, 'created_at' => now()->subDays(5)]);
    $newer = Project::factory()->create(['user_id' => $this->user->id, 'created_at' => now()->subDays(1)]);

    $response = $this->get(route('projects.index'));

    $projects = $response->viewData('projects');
    expect($projects->first()->id)->toBe($newer->id);
    expect($projects->last()->id)->toBe($older->id);
});

/**
 * ==========================================
 * Create Tests
 * ==========================================
 */
test('authenticated user can access create form', function () {
    $response = $this->get(route('projects.create'));

    $response->assertSuccessful();
    $response->assertViewIs('projects.create');
});

/**
 * ==========================================
 * Store Tests
 * ==========================================
 */
test('authenticated user can create project', function () {
    $response = $this->post(route('projects.store'), [
        'name' => 'New Project',
        'description' => 'Project description',
        'status' => ProjectStatus::Active->value,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('projects', [
        'name' => 'New Project',
        'description' => 'Project description',
        'status' => ProjectStatus::Active->value,
        'user_id' => $this->user->id,
    ]);
});

test('store requires name', function () {
    $response = $this->post(route('projects.store'), [
        'description' => 'Project description',
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('store name must be string', function () {
    $response = $this->post(route('projects.store'), [
        'name' => 123,
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('store name must have maximum length', function () {
    $response = $this->post(route('projects.store'), [
        'name' => str_repeat('a', 256),
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('store description is optional', function () {
    $response = $this->post(route('projects.store'), [
        'name' => 'Project without description',
        'status' => ProjectStatus::Draft->value,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('projects', [
        'name' => 'Project without description',
        'user_id' => $this->user->id,
    ]);
});

test('store status must be valid enum value', function () {
    $response = $this->post(route('projects.store'), [
        'name' => 'Test Project',
        'status' => 'invalid_status',
    ]);

    $response->assertSessionHasErrors(['status']);
});

test('created project is owned by authenticated user', function () {
    $otherUser = User::factory()->create();

    $this->post(route('projects.store'), [
        'name' => 'Test Project',
        'description' => 'Test description',
        'status' => ProjectStatus::Draft->value,
        'user_id' => $otherUser->id, // Attempt to set different user_id
    ]);

    $project = Project::where('name', 'Test Project')->first();
    expect($project->user_id)->toBe($this->user->id);
});

/**
 * ==========================================
 * Show Tests
 * ==========================================
 */
test('authenticated user can view own project', function () {
    $project = Project::factory()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('projects.show', $project));

    $response->assertSuccessful();
    $response->assertViewIs('projects.show');
    $response->assertViewHas('project');
    $response->assertSee($project->name);
});

test('user cannot view other users project', function () {
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->get(route('projects.show', $project));

    $response->assertForbidden();
});

/**
 * ==========================================
 * Edit Tests
 * ==========================================
 */
test('authenticated user can edit own project', function () {
    $project = Project::factory()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('projects.edit', $project));

    $response->assertSuccessful();
    $response->assertViewIs('projects.edit');
    $response->assertViewHas('project');
    $response->assertSee($project->name);
});

test('user cannot edit other users project', function () {
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->get(route('projects.edit', $project));

    $response->assertForbidden();
});

/**
 * ==========================================
 * Update Tests
 * ==========================================
 */
test('authenticated user can update own project', function () {
    $project = Project::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Original Name',
    ]);

    $response = $this->put(route('projects.update', $project), [
        'name' => 'Updated Name',
        'description' => 'Updated description',
        'status' => ProjectStatus::Completed->value,
    ]);

    $response->assertRedirect(route('projects.show', $project));
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated Name',
        'description' => 'Updated description',
        'status' => ProjectStatus::Completed->value,
    ]);
});

test('user cannot update other users project', function () {
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->put(route('projects.update', $project), [
        'name' => 'Hacked Name',
        'description' => $project->description,
        'status' => $project->status->value,
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => $project->name, // Should remain unchanged
    ]);
});

test('update requires name', function () {
    $project = Project::factory()->create(['user_id' => $this->user->id]);

    $response = $this->put(route('projects.update', $project), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('update status must be valid enum value', function () {
    $project = Project::factory()->create(['user_id' => $this->user->id]);

    $response = $this->put(route('projects.update', $project), [
        'name' => 'Test Project',
        'status' => 'invalid',
    ]);

    $response->assertSessionHasErrors(['status']);
});

/**
 * ==========================================
 * Destroy Tests
 * ==========================================
 */
test('authenticated user can delete own project', function () {
    $project = Project::factory()->create(['user_id' => $this->user->id]);

    $response = $this->delete(route('projects.destroy', $project));

    $response->assertRedirect(route('projects.index'));
    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});

test('user cannot delete other users project', function () {
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->delete(route('projects.destroy', $project));

    $response->assertForbidden();
    $this->assertDatabaseHas('projects', ['id' => $project->id]);
});

/**
 * ==========================================
 * Ownership Verification Tests
 * ==========================================
 */
test('user with multiple projects can only see their own', function () {
    $otherUser = User::factory()->create();

    $userProject1 = Project::factory()->create(['user_id' => $this->user->id, 'name' => 'User Project 1']);
    $userProject2 = Project::factory()->create(['user_id' => $this->user->id, 'name' => 'User Project 2']);
    $otherProject = Project::factory()->create(['user_id' => $otherUser->id, 'name' => 'Other Project']);

    $response = $this->get(route('projects.index'));

    $response->assertSee('User Project 1');
    $response->assertSee('User Project 2');
    $response->assertDontSee('Other Project');
});

test('cannot access show for non-existent project', function () {
    $response = $this->get(route('projects.show', 99999));

    $response->assertNotFound();
});

test('cannot access edit for non-existent project', function () {
    $response = $this->get(route('projects.edit', 99999));

    $response->assertNotFound();
});

test('cannot update non-existent project', function () {
    $response = $this->put(route('projects.update', 99999), [
        'name' => 'Test',
    ]);

    $response->assertNotFound();
});

test('cannot delete non-existent project', function () {
    $response = $this->delete(route('projects.destroy', 99999));

    $response->assertNotFound();
});
