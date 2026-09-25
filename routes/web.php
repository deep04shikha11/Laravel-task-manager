<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $firstProject = Project::query()->orderBy('name')
        ->first();

    return $firstProject
        ? redirect()->route('projects.tasks.index', $firstProject)
        : view('projects.create');
})->name('home');

Route::get('projects/create', [ProjectController::class, 'create'])
    ->name('projects.create');
Route::post('projects', [ProjectController::class, 'store'])
    ->name('projects.store');
Route::get('projects/{project}/tasks', [TaskController::class, 'index'])
    ->name('projects.tasks.index');
Route::post('tasks', [TaskController::class, 'store'])
    ->name('tasks.store');
Route::get('tasks/reorder', [TaskController::class, 'reorder'])
    ->name('tasks.reorder');
Route::get('tasks/{task}/edit', [TaskController::class, 'edit'])
    ->name('tasks.edit');
Route::put('tasks/{task}', [TaskController::class, 'update'])
    ->name('tasks.update');
Route::delete('tasks/{task}', [TaskController::class, 'destroy'])
    ->name('tasks.destroy');