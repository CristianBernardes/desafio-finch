<?php

namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the Task "creating" event.
     * Este evento é disparado ANTES de salvar no banco
     */
    public function creating(Task $task): void
    {
        // Se está criando com status completed e não tem completed_in, preenche
        if ($task->status === Task::STATUS_COMPLETED && !$task->completed_in) {
            $task->completed_in = now();
        }
    }

    /**
     * Handle the Task "updating" event.
     * Este evento é disparado ANTES de atualizar no banco
     */
    public function updating(Task $task): void
    {
        // Se está mudando para completed e não tem completed_in, preenche automaticamente
        if ($task->isDirty('status') && $task->status === Task::STATUS_COMPLETED && !$task->completed_in) {
            $task->completed_in = now();
        }

        // Nota: A validação que impede alterar tarefas completed está no TaskService
        // Este observer apenas gerencia o campo completed_in automaticamente
    }
}
