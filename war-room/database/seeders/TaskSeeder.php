<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verifica se existem usuários no banco
        $userCount = User::count();

        if ($userCount === 0) {
            $this->command->warn('Nenhum usuário encontrado. Execute UserSeeder primeiro.');
            return;
        }

        $this->command->info("Criando 100 tarefas aleatórias...");

        // Cria 100 tarefas usando a factory
        Task::factory()->count(100)->create();

        // Estatísticas
        $pending = Task::where('status', Task::STATUS_PENDING)->count();
        $inProgress = Task::where('status', Task::STATUS_IN_PROGRESS)->count();
        $completed = Task::where('status', Task::STATUS_COMPLETED)->count();

        $this->command->info("✓ 100 tarefas criadas com sucesso!");
        $this->command->info("  - Pendentes: {$pending}");
        $this->command->info("  - Em andamento: {$inProgress}");
        $this->command->info("  - Concluídas: {$completed}");
    }
}
