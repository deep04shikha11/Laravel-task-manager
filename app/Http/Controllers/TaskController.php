<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Http\Requests\ReorderTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller {

    public function index(Project $project) {
        return view('tasks.index', [
            'projects' => Project::query()->orderBy('name')->get(),
            'project' => $project,
            'tasks' => $project->tasks,
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse {
        $project = Project::findOrFail($request->validated('project_id'));
        $project->tasks()->create([
            'name' => $request->validated('name'),
            'priority' => $project->tasks()->max('priority') + 1,
        ]);

        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('status', "Task \"{$request->validated('name')}\" created.");
    }

    public function edit(Task $task): View {
        return view('tasks.edit', [
            'task' => $task,
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse {
        $task->update($request->validated());
        return redirect()
            ->route('projects.tasks.index', $task->project)
            ->with('status', "Task \"{$task->name}\" updated.");
    }

    public function destroy(Task $task): RedirectResponse {
        $project = $task->project;
        $task->delete();
        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('status', "Task \"{$task->name}\" deleted.");
    }

    public function reorder(ReorderTasksRequest $request): JsonResponse {
        $taskIDs = $request->validated('task_ids');

        DB::transaction(function () use ($taskIDs) {
            foreach ($taskIDs as $index => $taskID) {
                Task::whereKey($taskID)->update(['priority' => $index + 1]);
            }
        });
        return response()->json(['status' => 'ok']);
    }
}