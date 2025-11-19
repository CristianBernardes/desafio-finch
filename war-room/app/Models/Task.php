<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    const STATUS_TRANSLATION = [
        self::STATUS_PENDING => 'Pendente',
        self::STATUS_IN_PROGRESS => 'Em Andamento',
        self::STATUS_COMPLETED => 'Completo',
    ];

    /**
     * Define as transições de status permitidas
     * Cada status pode transicionar apenas para os status listados em seu array
     */
    const ALLOWED_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED],
        self::STATUS_IN_PROGRESS => [self::STATUS_COMPLETED, self::STATUS_PENDING],
        self::STATUS_COMPLETED => [], // Tarefas concluídas não podem mudar de status
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'assigned_to',
        'completed_in',
    ];

    protected $casts = [
        'assigned_to' => 'integer',
        'completed_in' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relacionamento com o usuário atribuído
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Verifica se a tarefa pode transicionar para um determinado status
     *
     * @param string $status
     * @return bool
     */
    public function canTransitionTo(string $status): bool
    {
        // Não pode transicionar para o mesmo status
        if ($this->status === $status) {
            return false;
        }

        // Verifica se o status de destino é válido
        if (!in_array($status, self::getValidStatuses())) {
            return false;
        }

        $allowedTransitions = self::ALLOWED_TRANSITIONS[$this->status] ?? [];
        return in_array($status, $allowedTransitions);
    }

    /**
     * Retorna os status disponíveis para esta tarefa específica
     *
     * @return array
     */
    public function getAvailableTransitions(): array
    {
        return self::ALLOWED_TRANSITIONS[$this->status] ?? [];
    }

    /**
     * Retorna todos os status válidos (método estático para uso em validações)
     *
     * @return array
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
        ];
    }

    /**
     * Retorna a tradução do status atual
     *
     * @return string
     */
    public function getStatusTranslation(): string
    {
        return self::STATUS_TRANSLATION[$this->status] ?? $this->status;
    }

    /**
     * Verifica se a tarefa está pendente
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica se a tarefa está em andamento
     *
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Verifica se a tarefa está concluída
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Marca a tarefa como concluída
     *
     * @return bool
     */
    public function markAsCompleted(): bool
    {
        if (!$this->canTransitionTo(self::STATUS_COMPLETED)) {
            return false;
        }

        $this->status = self::STATUS_COMPLETED;
        $this->completed_in = now();
        return $this->save();
    }

    /**
     * Serializa a data no formato ISO 8601
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
