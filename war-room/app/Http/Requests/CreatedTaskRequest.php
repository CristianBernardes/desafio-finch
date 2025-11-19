<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatedTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(Task::getValidStatuses())],
            'assigned_to' => 'nullable|exists:users,id',
            'completed_in' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.string' => 'O título deve ser um texto.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'description.string' => 'A descrição deve ser um texto.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status selecionado é inválido. Valores permitidos: pendente, em andamento ou concluído.',
            'assigned_to.exists' => 'O usuário selecionado não existe.',
            'completed_in.date' => 'A data de conclusão deve ser uma data válida.',
        ];
    }
}
