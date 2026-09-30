@extends('layouts.app')
@section('title', $project->name . ' — Tasks')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <label for="project-switcher" class="form-label small text-muted mb-1">Project</label>
        <select id="project-switcher" class="form-select">
            @foreach ($projects as $option)
            <option value="{{ route('projects.tasks.index', $option) }}" @selected($option->id === $project->id)>
                {{ $option->name }}
            </option>
            @endforeach
        </select>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse"
        data-bs-target="#new-project-form">
        + New project
    </button>
</div>
<div id="new-project-form" class="collapse mb-4">
    <form method="POST" action="{{ route('projects.store') }}" class="d-flex gap-2">
        @csrf
        <input type="text" name="name" class="form-control" placeholder="Project name" required>
        <button type="submit" class="btn btn-secondary text-nowrap">Create</button>
    </form>
</div>
<div class="card shadow-sm mb-4">
    <div class="card-body p-4">
        <h1 class="h4 mb-3">{{ $project->name }}</h1>

        <form method="POST" action="{{ route('tasks.store') }}" class="d-flex gap-2 mb-4">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <input type="text" name="name" class="form-control" placeholder="What needs to be done?"
                value="{{ old('name') }}" required>
            <button type="submit" class="btn btn-primary text-nowrap">Add task</button>
        </form>

        @if ($tasks->isEmpty())
        <p class="text-muted mb-0">No tasks in this project yet. Add one above to get started.</p>
        @else
        <p class="small text-muted">Drag tasks to reorder. #1 is the top (highest) priority.</p>

        <ul id="task-list" class="list-group" data-project-id="{{ $project->id }}">
            @foreach ($tasks as $task)
            <li class="list-group-item d-flex align-items-center gap-3" data-task-id="{{ $task->id }}">
                <span class="drag-handle text-muted" title="Drag to reorder">⠿⠿</span>
                <span class="badge bg-secondary rounded-pill task-rank">{{ $loop->iteration }}</span>
                <span class="flex-grow-1">{{ $task->name }}</span>
                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                    onsubmit="return confirm('Delete this task?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    document.getElementById('project-switcher').addEventListener('change', function(event) {
        window.location = event.target.value;
    });

    const taskList = document.getElementById('task-list');

    if (taskList) {
        Sortable.create(taskList, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function() {
                updateRankBadges();
                saveNewOrder();
            },
        });
    }

    function updateRankBadges() {
        taskList.querySelectorAll('.task-rank').forEach((badge, index) => badge.textContent = index + 1);
    }

    function saveNewOrder() {
        const taskIds = Array.from(taskList.children).map((item) => item.dataset.taskId);

        fetch(`{{ route('tasks.reorder') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                project_id: Number(taskList.dataset.projectId),
                task_ids: taskIds,
            }),
        }).catch(() => alert('Could not save the new order. Please refresh and try again.'));
    }
</script>
@endpush