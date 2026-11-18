<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Project;
use App\Models\Task;

class TaskManagementTest extends TestCase {
    use RefreshDatabase;

    public function test_home_redirects_to_project_creation_when_no_projects_exist(): void {
        $this->get('/')->assertRedirect(route('projects.create'));
    }

    public function test_a_project_can_be_created(): void {
        $response = $this->post(route('projects.store'), ['name' => 'Work']);

        $project = Project::firstOrFail();
        $response->assertRedirect(route('projects.tasks.index', $project));
        $this->assertSame('Work', $project->name);
    }

    public function test_a_task_is_appended_to_the_bottom_of_its_project(): void {
        $project = Project::factory()->create();
        Task::factory()->create(['project_id' => $project->id, 'priority' => 1]);
        Task::factory()->create(['project_id' => $project->id, 'priority' => 2]);

        $this->post(route('tasks.store'), [
            'project_id' => $project->id,
            'name' => 'New task',
        ])->assertRedirect(route('projects.tasks.index', $project));

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'name' => 'New task',
            'priority' => 3,
        ]);
    }

    public function test_a_task_requires_a_name(): void {
        $project = Project::factory()->create();

        $this->post(route('tasks.store'), ['project_id' => $project->id, 'name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_a_task_can_be_updated(): void {
        $task = Task::factory()->create(['name' => 'Old name']);

        $this->put(route('tasks.update', $task), ['name' => 'New name'])
            ->assertRedirect(route('projects.tasks.index', $task->project));

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'name' => 'New name']);
    }
    public function test_a_task_can_be_deleted(): void {
        $task = Task::factory()->create();

        $this->delete(route('tasks.destroy', $task))
            ->assertRedirect(route('projects.tasks.index', $task->project));

        $this->assertModelMissing($task);
    }
    public function test_tasks_can_be_reordered_and_priorities_are_updated(): void {
        $project = Project::factory()->create();
        $first = Task::factory()->create(['project_id' => $project->id, 'priority' => 1]);
        $second = Task::factory()->create(['project_id' => $project->id, 'priority' => 2]);
        $third = Task::factory()->create(['project_id' => $project->id, 'priority' => 3]);

        $this->postJson(route('tasks.reorder'), [
            'project_id' => $project->id,
            'task_ids' => [$third->id, $first->id, $second->id],
        ])->assertOk();

        $this->assertSame(1, $third->fresh()->priority);
        $this->assertSame(2, $first->fresh()->priority);
        $this->assertSame(3, $second->fresh()->priority);
    }
    public function test_reorder_rejects_tasks_from_another_project(): void {
        $project = Project::factory()->create();
        $otherTask = Task::factory()->create(['priority' => 1]);

        $this->postJson(route('tasks.reorder'), [
            'project_id' => $project->id,
            'task_ids' => [$otherTask->id],
        ])->assertUnprocessable();

        $this->assertSame(1, $otherTask->fresh()->priority);
    }
    public function test_the_task_list_only_shows_the_selected_projects_tasks(): void {
        $work = Project::factory()->create(['name' => 'Work']);
        $personal = Project::factory()->create(['name' => 'Personal']);
        Task::factory()->create(['project_id' => $work->id, 'name' => 'Write report']);
        Task::factory()->create(['project_id' => $personal->id, 'name' => 'Buy groceries']);

        $this->get(route('projects.tasks.index', $work))
            ->assertOk()
            ->assertSee('Write report')
            ->assertDontSee('Buy groceries');
    }
}