<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderTasksRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => [
                'integer',
                Rule::exists('tasks', 'id')->where(
                    fn($query) => $query->where('project_id', $this->input('project_id'))
                ),
            ],
        ];
    }
}