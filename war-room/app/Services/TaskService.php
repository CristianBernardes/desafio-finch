<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Service para gerenciamento de tarefas
 */
class TaskService extends BaseService
{
    /**
     * @return Model
     */
    protected function model(): Model
    {
        return new Task();
    }

    /**
     * Cria uma nova tarefa
     *
     * @param array $data
     * @return Task
     * @throws \Exception
     */
    public function create(array $data): Task
    {
        try {
            $status = $data['status'] ?? Task::STATUS_PENDING;

            $task = $this->model()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $status,
                'assigned_to' => $data['assigned_to'] ?? null,
                'completed_in' => $data['completed_in'] ?? null,
            ]);

            return $task->load('assignedUser:id,name,email');
        } catch (\Exception $e) {
            Log::error('Erro ao criar tarefa', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Lista tarefas com filtros e paginação
     *
     * @param array $filters
     * @return LengthAwarePaginator
     * @throws \Exception
     */
    public function listTasks(array $filters): LengthAwarePaginator
    {
        try {
            $tasks = Task::query()
                ->with('assignedUser:id,name,email')
                ->when($filters['title'] ?? null, function (Builder $query, string $title) {
                    $query->where('title', 'like', '%' . $title . '%');
                })
                ->when($filters['status'] ?? null, function (Builder $query, string $status) {
                    $query->where('status', $status);
                })
                ->when($filters['assigned_to'] ?? null, function (Builder $query, string $assignedTo) {
                    $query->where('assigned_to', $assignedTo);
                })
                ->when($filters['description'] ?? null, function (Builder $query, string $description) {
                    $query->where('description', 'like', '%' . $description . '%');
                });

            $sortField = $filters['sort'] ?? 'created_at';
            $sortOrder = strtolower($filters['order'] ?? 'desc');

            $allowedSortFields = ['title', 'status', 'assigned_to', 'created_at', 'updated_at'];
            $allowedSortOrders = ['asc', 'desc'];

            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'created_at';
            }

            if (!in_array($sortOrder, $allowedSortOrders)) {
                $sortOrder = 'desc';
            }

            $tasks->orderBy($sortField, $sortOrder);

            $perPage = min((int)($filters['per_page'] ?? 20), 100);
            $page = (int)($filters['page'] ?? 1);

            return $tasks->paginate($perPage, ['*'], 'page', $page);
        } catch (\Exception $e) {
            Log::error('Erro ao listar tarefas', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'filters' => $filters,
            ]);

            throw $e;
        }
    }

    /**
     * Busca uma tarefa por ID
     *
     * @param int $id
     * @return Task
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Task
    {
        try {
            $task = Task::with('assignedUser:id,name,email')->find($id);

            if (!$task) {
                throw new ModelNotFoundException('Tarefa não encontrada');
            }

            return $task;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar tarefa', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'id' => $id,
            ]);

            throw $e;
        }
    }

    /**
     * Atualiza uma tarefa existente
     *
     * @param int $id
     * @param array $data
     * @return Task
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function updateTask(int $id, array $data): Task
    {
        try {
            $task = $this->findById($id);

            // Valida se está tentando alterar uma tarefa concluída
            if (isset($data['status']) && $task->status === Task::STATUS_COMPLETED && $data['status'] !== Task::STATUS_COMPLETED) {
                throw new \Exception(
                    "Não é possível alterar o status de uma tarefa já concluída."
                );
            }

            // Valida transição de status se estiver sendo alterado
            if (isset($data['status']) && $data['status'] !== $task->status) {
                if (!$task->canTransitionTo($data['status'])) {
                    throw new \Exception(
                        "Não é possível alterar o status de '{$task->getStatusTranslation()}' para '" .
                            (Task::STATUS_TRANSLATION[$data['status']] ?? $data['status']) . "'"
                    );
                }
            }

            // Atualiza a tarefa - o Observer vai cuidar do completed_in automaticamente
            $task->update($data);

            return $task->fresh(['assignedUser:id,name,email']);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar tarefa', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Deleta uma tarefa (soft delete)
     *
     * @param int $id
     * @return bool
     * @throws ModelNotFoundException
     */
    public function deleteTask(int $id): bool
    {
        try {
            $task = $this->findById($id);
            return $task->delete();
        } catch (\Exception $e) {
            Log::error('Erro ao deletar tarefa', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'id' => $id,
            ]);

            throw $e;
        }
    }
}
