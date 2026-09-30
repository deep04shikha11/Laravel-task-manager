@extends('layouts.app')

@section('title', 'Create a project')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Welcome 👋</h1>
                <p class="text-muted">Every task belongs to a project. Create your first one to get started.</p>
                <form method="POST" action="{{ route('projects.store') }}" class="d-flex gap-2 mt-3">
                    @csrf
                    <input type="text" name="name" class="form-control" placeholder="e.g. Work, Personal, Side Project"
                        value="{{ old('name') }}" autofocus required>
                    <button type="submit" class="btn btn-primary text-nowrap">Create project</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection