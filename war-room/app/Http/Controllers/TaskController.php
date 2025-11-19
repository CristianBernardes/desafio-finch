<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatedTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'status' => $request->input('status'),
                'title' => $request->input('title'),
                'assigned_to' => $request->input('assigned_to'),
                'description' => $request->input('description'),
                'sort' => $request->input('sort'),
                'order' => $request->input('order'),
                'per_page' => $request->input('per_page'),
                'page' => $request->input('page'),
            ];

            $tasks = $this->taskService->listTasks($filters);
            return response()->json([
                'message' => 'Tarefas recuperadas com sucesso',
                'data' => TaskResource::collection($tasks->items()),
                'meta' => [
                    'current_page' => $tasks->currentPage(),
                    'from' => $tasks->firstItem(),
                    'last_page' => $tasks->lastPage(),
                    'per_page' => $tasks->perPage(),
                    'to' => $tasks->lastItem(),
                    'total' => $tasks->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar tarefas'], 500);
        }
    }

    public function store(CreatedTaskRequest $request)
    {
        try {
            $task = $this->taskService->create($request->validated());
            return response()->json([
                'message' => 'Tarefa criada com sucesso',
                'data' => new TaskResource($task)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao criar tarefa'], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $task = $this->taskService->findById($id);
            return response()->json([
                'message' => 'Tarefa encontrada',
                'data' => new TaskResource($task)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Tarefa não encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar tarefa'], 500);
        }
    }

    public function update(UpdateTaskRequest $request, int $id)
    {
        try {
            $this->taskService->updateTask($id, $request->validated());

            // Recarrega a task com o relacionamento atualizado
            $updatedTask = $this->taskService->findById($id);

            return response()->json([
                'message' => 'Tarefa atualizada com sucesso',
                'data' => new TaskResource($updatedTask)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Tarefa não encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->taskService->deleteTask($id);
            return response()->json(['message' => 'Tarefa deletada com sucesso']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Tarefa não encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao deletar tarefa'], 500);
        }
    }
}
