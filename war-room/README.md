# API de Gerenciamento de Tarefas

Sistema de gerenciamento de tarefas desenvolvido com Laravel 10+ e PostgreSQL, utilizando JWT para autenticação.

## 🚀 Tecnologias

- **Laravel 10+** - Framework PHP
- **PostgreSQL** - Banco de dados
- **JWT Auth** - Autenticação via tokens
- **Docker** - Containerização
- **PHP 8.3** - Linguagem

## 📋 Pré-requisitos

- Docker
- Docker Compose
- Git

## 🔧 Instalação e Configuração

### 1. Clone o repositório

```bash
git clone https://github.com/CristianBernardes/desafio-finch.git
cd desafio-finch
```

### 2. Subir os containers Docker

```bash
docker compose up -d
```

Este comando irá subir os seguintes containers:
- `php83-app` - Aplicação PHP/Laravel
- `postgres` - Banco de dados PostgreSQL
- `nginx` - Servidor web

### 3. Acessar o container PHP

```bash
docker exec -it php83-app bash
```

### 4. Instalar dependências do Composer

```bash
composer install
```

### 5. Configurar o arquivo .env

Copie o arquivo de exemplo:

```bash
cp .env.example .env
```

O arquivo `.env.example` já vem com todas as configurações necessárias, incluindo:
- `APP_KEY` - Chave da aplicação
- `JWT_SECRET` - Chave para autenticação JWT
- Configurações do banco de dados

### 6. Executar as migrations

```bash
php artisan migrate
```

### 7. (Opcional) Popular o banco com dados de exemplo

```bash
php artisan db:seed
```

Isso irá criar:
- 3 usuários de exemplo (admin, editor, viewer)
- 100 tarefas aleatórias

### 8. Sair do container

```bash
exit
```

## 🌐 Acessando a API

A API estará disponível em: `http://localhost:8080`

## 📚 Endpoints Disponíveis

### Autenticação

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/auth/register` | Registrar novo usuário |
| POST | `/api/auth/login` | Fazer login |
| POST | `/api/auth/logout` | Fazer logout |
| POST | `/api/auth/refresh` | Renovar token |
| GET | `/api/auth/me` | Dados do usuário autenticado |

### Tarefas (Requer autenticação)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/tasks` | Listar tarefas |
| GET | `/api/tasks/{id}` | Buscar tarefa por ID |
| POST | `/api/tasks` | Criar nova tarefa |
| PUT | `/api/tasks/{id}` | Atualizar tarefa |
| DELETE | `/api/tasks/{id}` | Deletar tarefa |

### Filtros disponíveis para listagem de tarefas:

- `status` - Filtrar por status (pending, in_progress, completed)
- `title` - Buscar por título (LIKE)
- `description` - Buscar por descrição (LIKE)
- `assigned_to` - Filtrar por usuário atribuído (ID)
- `sort` - Campo para ordenação (title, status, assigned_to, created_at, updated_at)
- `order` - Ordem (asc, desc)
- `per_page` - Itens por página (máx: 100)
- `page` - Número da página

## 🧪 Testando a API com Postman

1. Importe a collection localizada em: `postman_collection.json` (raiz do projeto, fora da pasta war-room)
2. Execute o endpoint de Login para gerar o token
3. Copie o token retornado no campo `access_token`
4. Cole o token no header `Authorization` dos demais endpoints no formato: `Bearer {seu-token}`

## 📖 Regras de Negócio

### Status de Tarefas

- **pending** (Pendente) - Estado inicial
- **in_progress** (Em Andamento)
- **completed** (Completo)

### Transições de Status Permitidas

- `pending` → `in_progress` ou `completed`
- `in_progress` → `completed` ou `pending`
- `completed` → **Nenhuma** (tarefas concluídas não podem ter status alterado)

### Funcionalidades Especiais

- ✅ Campo `completed_in` preenchido automaticamente ao marcar tarefa como concluída
- ✅ Tarefas concluídas não podem ter o status alterado
- ✅ Soft delete em tarefas (deletadas logicamente)
- ✅ Relacionamento com usuário (quando usuário é deletado, tarefa fica sem atribuição)
- ✅ Validações em português
- ✅ Paginação nas listagens

## 🛠️ Comandos Úteis

### Limpar caches

```bash
docker exec php83-app php artisan optimize:clear
```

### Ver logs

```bash
docker exec php83-app tail -f storage/logs/laravel.log
```

### Executar tinker (console interativo)

```bash
docker exec -it php83-app php artisan tinker
```

### Parar os containers

```bash
docker compose down
```

### Reiniciar os containers

```bash
docker compose restart
```

## 📁 Estrutura do Projeto

```
war-room/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   └── TaskController.php
│   │   ├── Requests/
│   │   │   ├── CreatedTaskRequest.php
│   │   │   └── UpdateTaskRequest.php
│   │   └── Resources/
│   │       └── TaskResource.php
│   ├── Models/
│   │   ├── Task.php
│   │   └── User.php
│   ├── Observers/
│   │   └── TaskObserver.php
│   └── Services/
│       ├── BaseService.php
│       ├── TaskService.php
│       └── AuthService.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
└── routes/
    └── api.php
```

## 🔐 Autenticação

A API utiliza JWT (JSON Web Tokens) para autenticação. Inclua o token no header das requisições:

```
Authorization: Bearer {seu-token-aqui}
```

## 📝 Exemplo de Uso

### 1. Registrar usuário

```bash
curl -X POST http://localhost:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@example.com",
    "password": "senha123",
    "password_confirmation": "senha123"
  }'
```

### 2. Fazer login

```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@example.com",
    "password": "senha123"
  }'
```

### 3. Criar tarefa

```bash
curl -X POST http://localhost:8080/api/tasks \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {seu-token}" \
  -d '{
    "title": "Implementar nova funcionalidade",
    "description": "Descrição detalhada da tarefa",
    "status": "pending",
    "assigned_to": 1
  }'
```

### 4. Listar tarefas

```bash
curl -X GET "http://localhost:8080/api/tasks?status=pending&per_page=10" \
  -H "Authorization: Bearer {seu-token}"
```

## 🐛 Troubleshooting

### Erro de permissão

```bash
docker exec php83-app chmod -R 777 storage bootstrap/cache
```

### Recriar o banco de dados

```bash
docker exec php83-app php artisan migrate:fresh --seed
```

### Ver logs de erro do container

```bash
docker logs php83-app
```

## 📄 Licença

Este projeto está sob a licença MIT.
