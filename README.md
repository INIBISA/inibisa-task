# IniBisa

Internal product and task management workspace for the IniBisa team.

IniBisa connects audience, product, task, idea, and team workload in one private web application. It answers: what is being built, who owns it, and what happens next.

## Features

- Internal authentication. Public registration disabled.
- Audience and product management.
- Product-scoped task board with search, status filter, priority, deadlines, assignees, and subtasks.
- Drag tasks between workflow columns: Backlog, Planned, In Progress, Review, Done.
- Priority badges: Mendesak, Tinggi, Normal, Rendah.
- Idea inbox with idea-to-product conversion.
- Team member CRUD for admins: create, edit, reset password, deactivate.
- Product progress calculated from completed tasks.
- Audit activities stored for future notification use.

## Stack

- PHP 8.2+
- Laravel 12
- MySQL 8+
- Blade and Tailwind CSS
- Vite

## Requirements

- PHP 8.2 or newer
- Composer
- Node.js 20 or newer
- MySQL 8 or newer

## Setup

1. Install dependencies.

```bash
composer install
npm install
```

2. Create the database.

```sql
CREATE DATABASE inibisa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Configure `.env`.

```env
APP_NAME=IniBisa
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inibisa
DB_USERNAME=root
DB_PASSWORD=
```

4. Generate app key, migrate, and seed demo data.

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

5. Run the application.

```bash
php artisan serve
npm run dev
```

Open `http://127.0.0.1:8000`.

## Demo Login

```text
Email: hael@inibisa.test
Password: password
```

The seeded account is an admin. Use Team to create or deactivate members.

## Commands

```bash
# Run tests
php artisan test

# Production frontend build
npm run build

# Apply new migrations
php artisan migrate

# Reset local database with demo data
php artisan migrate:fresh --seed
```

## Workflow

```text
Audience -> Product -> Task -> Subtask
Idea -> Approved -> Product
```

Task board workflow:

```text
Backlog -> Planned -> In Progress -> Review -> Done
```

Drag a task from its `⠿` handle to move it between columns.

## Access Control

- All workspace routes require authentication.
- `admin`: manages team members.
- `member`: works with products, tasks, and ideas.
- Deactivated members cannot sign in. Their historic tasks and activity remain available.

## Realtime

The current version uses standard page updates. Laravel Reverb/Echo is intentionally not configured yet. Add it when concurrent realtime collaboration needs justify infrastructure.

## Product Specification

Full requirements: [`prd-inibisa-task-management.md`](prd-inibisa-task-management.md).
