<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller {

    public function create(): View | RedirectResponse {
        if (Project::query()->exists()) {
            return redirect()->route('home');
        }
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse {
        $project = Project::create($request->validated());
        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('status', "Project \"{$project->name}\" created.");
    }
}