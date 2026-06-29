# Sistema de Agendamento

Sistema web para gerenciamento de agendamentos de atendimentos e disponibilidade de agenda.

**Processo Seletivo 01873/2026 — Desenvolvedor Full Stack Pleno — SENAI/SC**

---

## Stack

| Camada | Tecnologia |
|--------|-----------|
| Backend | PHP 8.5, Laravel 13 |
| Frontend | Vue 3 (Composition API), Tailwind CSS 4 |
| Calendário | FullCalendar Vue 3 |
| Banco | MySQL 8 |
| Auth | Laravel Sanctum (cookie SPA) |
| Infra | Docker Compose |
| Testes | PHPUnit |

---

## Como Rodar

### Pré-requisitos
- Docker e Docker Compose instalados

### Setup (um comando)

```bash
docker-compose up -d --build
```

Aguarde o backend conectar ao MySQL (logs mostram "Starting server...").

### Acessar

| Serviço | URL |
|---------|-----|
| Frontend | http://localhost:5173 |
| Backend API | http://localhost:8000/api |

### Credenciais (seed)

| Perfil | E-mail | Senha |
|--------|--------|-------|
| Administrador | admin@email.com | password |
| Atendente | joao@email.com | password |
| Atendente | maria@email.com | password |

---

## Funcionalidades

### Módulo de Usuários
- Listagem para todos os perfis
- Administrador: criar, editar qualquer usuário, excluir (com confirmação)
- Atendente: editar apenas o próprio perfil
- Validação: email único, senha mín. 8 caracteres, confirmação de senha
- Soft delete

### Módulo de Clientes
- CRUD completo (nome, telefone, email)
- Busca por nome ou email
- Soft delete

### Módulo de Disponibilidade
- Administrador cadastra janelas de horário por atendente
- Campos: atendente, dia da semana, hora inicial/final, ativo
- Validação: hora final > hora inicial
- Múltiplas janelas por dia (manhã/tarde)
- Soft delete

### Módulo de Agendamentos
- Consulta de horários disponíveis (atendente + data → slots de 30min)
- Slots ocupados não aparecem como opção
- Criação com validação de conflito no backend
- Cancelamento com soft delete
- Visualização em calendário (FullCalendar)
- Cores por status (azul=agendado, verde=concluído, vermelho=cancelado)

### Auditoria
- Log de criação, edição e exclusão em todos os módulos
- Registra: usuário, ação, modelo, valores antigos/novos, IP

---

## Arquitetura

```
Request → FormRequest (valida) → Controller → Service → Repository → Model
                                      ↓
                              Resource (formata JSON) → Response
```

| Camada | Responsabilidade |
|--------|-----------------|
| Controller | Recebe, delega, retorna |
| Form Request | Validação com mensagens pt-BR |
| Service | Regras de negócio |
| Repository | Queries e persistência |
| Resource | Serialização JSON |
| Model | Eloquent + Soft Deletes |
| Middleware | Controle de acesso (role) |

---

## API Endpoints

### Auth
| Método | Rota | Descrição |
|--------|------|-----------|
| POST | /api/login | Login |
| POST | /api/logout | Logout |
| GET | /api/me | Usuário autenticado |

### Usuários
| Método | Rota | Acesso |
|--------|------|--------|
| GET | /api/users | todos |
| POST | /api/users | admin |
| PUT | /api/users/:id | admin (todos) / atendente (só ele) |
| DELETE | /api/users/:id | admin |

### Clientes
| Método | Rota | Acesso |
|--------|------|--------|
| GET | /api/clients | todos |
| GET | /api/clients/all | todos |
| POST | /api/clients | todos |
| PUT | /api/clients/:id | todos |
| DELETE | /api/clients/:id | todos |

### Disponibilidade
| Método | Rota | Acesso |
|--------|------|--------|
| GET | /api/availabilities | admin |
| POST | /api/availabilities | admin |
| PUT | /api/availabilities/:id | admin |
| DELETE | /api/availabilities/:id | admin |

### Agendamentos
| Método | Rota | Acesso |
|--------|------|--------|
| GET | /api/appointments | todos |
| POST | /api/appointments | todos |
| PATCH | /api/appointments/:id/cancel | todos |
| GET | /api/slots?user_id=&date= | todos |

### Auditoria
| Método | Rota | Acesso |
|--------|------|--------|
| GET | /api/audit-logs | admin |

---

## Testes

```bash
docker run --rm --network agendamento-senai_default \
  -v "$(pwd)/backend:/app" -w /app \
  -e APP_KEY=base64:B4EsiIuVkaFFd2XhjniEET+OX1LVim3DyI33r7yBoAA= \
  -e DB_CONNECTION=mysql -e DB_HOST=mysql -e DB_PORT=3306 \
  -e DB_DATABASE=agendamento -e DB_USERNAME=agendamento -e DB_PASSWORD=secret \
  php:8.5-cli bash -c "apt-get update -qq > /dev/null && apt-get install -y -qq unzip libzip-dev > /dev/null && docker-php-ext-install zip pdo_mysql > /dev/null && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer > /dev/null && composer install -q && php vendor/bin/phpunit"
```

39 testes cobrindo:
- Autenticação (login válido/inválido, acesso protegido)
- CRUD de usuários (permissões admin vs atendente)
- CRUD de clientes (validação, soft delete)
- Disponibilidade (validação hora, permissões)
- Slots (geração, exclusão de ocupados, dias sem disponibilidade)
- Agendamentos (criação, conflito, cancelamento)

---

## Decisões Técnicas

- **Laravel Sanctum (cookie)**: auth stateful ideal para SPA, sem tokens expostos no localStorage
- **Slots de 30 minutos**: padrão de atendimento razoável para a maioria dos serviços
- **Soft Delete em todos os models**: preserva integridade de dados e histórico
- **Audit Logs**: rastreabilidade de operações (quem fez o quê, quando, de onde)
- **CRUD de Clientes**: a prova permite mocks, mas um cadastro real demonstra design de banco correto com FK no agendamento
- **FullCalendar**: visualização profissional da agenda, demonstra integração com bibliotecas externas
- **Tailwind CSS 4**: estilos utilitários para agilidade sem sacrificar qualidade visual
- **Componentes reutilizáveis**: Modal, TextInput, SelectInput, Alert — evita repetição de código

---

## Estrutura do Projeto

```
agendamento-senai/
├── docker-compose.yml
├── README.md
├── backend/
│   ├── app/Http/Controllers/Api/    (6 controllers)
│   ├── app/Http/Requests/           (7 form requests)
│   ├── app/Http/Resources/          (5 resources)
│   ├── app/Http/Middleware/         (CheckRole)
│   ├── app/Services/               (5 services)
│   ├── app/Repositories/           (4 repositories)
│   ├── app/Models/                 (5 models)
│   ├── database/migrations/        (5 migrations)
│   ├── database/seeders/           (4 seeders)
│   ├── tests/Feature/             (6 test files, 39 testes)
│   └── routes/api.php
└── frontend/
    ├── src/views/                  (6 páginas)
    ├── src/components/             (11 componentes reutilizáveis)
    ├── src/composables/            (useAuth)
    ├── src/services/               (api.js)
    ├── src/utils/                  (masks.js)
    └── src/router/                 (guards por role)
```
