# AGENTS.md - HelpDesk AI

## Project Overview

HelpDesk AI is a Laravel 13.8 backend API application with integrated AI capabilities via Groq API. The project is a "fil rouge" (capstone project) demonstrating end-to-end backend development: API design, AI integration, testing, Docker containerization, CI/CD, and deployment.

**Stack:**
- PHP 8.3+
- Laravel 13.8
- SQLite (testing) / MySQL (production)
- Groq API for AI features (OpenAI-compatible endpoints)
- Pest/PHPUnit for testing
- Docker for containerization
- GitHub Actions for CI

---

## Project Context & Timeline

### Formation
- **Diplome:** Developpeur Web et Web Mobile (DWWM) / Backend
- **Certification:** [2023] Developpeur web et web mobile
- **Mode:** Individuel
- **Lancement:** 13/07/2026
- **Date limite de soumission:** 07/08/2026
- **Soutenances:** a partir du 10/08/2026

### Compétences Mobilisees
- HTML5, CSS3, PHP, MySQL, JavaScript
- VueJS, React
- Git, SQL, UML
- IA Generative, Referentiels
- Agile (Scrum, Kanban)
- Accessibilite, Responsive Design

### Le Projet Fil Rouge
Le projet fil rouge est le projet de cloture de la formation. Il rassemble tout ce que tu as appris : concevoir, developper une API en Laravel, y integrer de l'IA, la tester, la conteneineriser avec Docker, l'automatiser avec une CI, la deployer en ligne, et la documenter. C'est ta preuve de competence, celle que tu montreras en soutenance et en entretien.

**Note sur l'IA:** Notre formation est un backend augmente par l'IA. L'IA intervient a deux niveaux dans ce projet. D'abord comme fonctionnalite : ton application integre une brique d'IA utile (via le SDK laravel/ai). Ensuite comme outil de travail : tu peux utiliser Claude Code, OpenCode ou un autre developper plus vite. Dans les deux cas, la regle est simple -- tu dois comprendre et savoir expliquer chaque ligne que tu livres.

---

## Contexte et Problematique

### Contexte
Une startup du nom de **HelpDesk AI** developpe une plateforme de support technique intelligent pour les entreprises. Leur objectif est de reduire le temps de traitement des tickets d'assistance en utilisant l'intelligence artificielle pour suggerer des reponses pre-redigees aux agents support.

### Problematique
Les entreprises recoivent des centaines de tickets de support par jour. Les agents support passent un temps considerable a repondre aux memes types de questions repetitives (questions frequentes, problemes connus, procedures standardisees). Cela entraine :
- **Lenteur** dans le traitement des tickets
- **Incoherence** dans les reponses apportees
- **Epuisement** des agents support
- **Insatisfaction client** due aux delais de reponse

### Solution Proposee
Une API REST intelligente qui :
1. **Recoit** le texte brut d'un ticket de support (description du probleme)
2. **Analyse** le contenu via l'IA (Groq API / LLM)
3. **Genere** automatiquement :
   - Un **hook** (accroche percutante pour la reponse)
   - Un **corps de reponse** structure avec les points techniques
   - Un **score de lisibilite technique** (0-100)
   - Des **hashtags suggeres** pour le分类 et la recherche
   - Une **justification du ton** (empathique, technique, direct, etc.)
4. **Stocke** la reponse proposee avec son statut (draft, approuve, modifie)
5. **Permet** a l'agent de modifier, approuver ou rejeter la suggestion

### Cas d'Utilisation Concret
> **Ticket:** "Mon application Laravel ne demarre plus, j'ai une erreur 500 sur toutes les pages"
>
> **Reponse IA generee:**
> - **Hook:** "Je comprends votre frustration. Une erreur 500 est souvent causee par un probleme de configuration."
> - **Corps:** "Voici les etapes pour resoudre votre probleme: 1) Verifiez les logs dans storage/logs/laravel.log, 2) Videz le cache: php artisan config:clear, 3) Verifiez la connexion a la base de donnees..."
> - **Score lisibilite:** 85/100
> - **Hashtags:** #laravel #erreur500 #debug #configuration
> - **Ton:** Technique et rassurant

---

## Fonctionnalites

### Fonctionnalites Principales (MVP)
| ID | Fonctionnalite | Priorite |
|----|----------------|----------|
| F1 | Creer un ticket avec texte brut | Haute |
| F2 | Generer une reponse IA a partir d'un ticket | Haute |
| F3 | Consulter le statut d'une generation | Haute |
| F4 | Lister tous les tickets | Haute |
| F5 | Modifier le statut d'une reponse (draft/approuve/rejete) | Haute |
| F6 | Authentification par token (Sanctum) | Haute |
| F7 | Dashboard Blade pour visualiser les tickets | Moyenne |
| F8 | Historique des versions d'une reponse | Basse |
| F9 | Exporter les reponses approuvees (CSV/PDF) | Basse |

### Fonctionnalites IA
- **Structured Output:** La reponse IA est toujours au meme format JSON (garanti par le schema)
- **Contexte multiple:** Support de plusieurs types de problemes (technique, facturation, compte)
- **Score de confiance:** L'IA indique sa confiance dans la reponse generee
- **Ton configurable:** Possibilite de demander un ton different (formel, amical, technique)

---

## User Stories

### En tant qu'agent support, je veux...
1. **US1:** Soumettre le texte d'un ticket pour obtenir une reponse suggeree
2. **US2:** Voir le hook propose, le corps de reponse, le score de lisibilite et les hashtags
3. **US3:** Modifier la reponse avant de l'envoyer au client
4. **US4:** Approuver ou rejeter une reponse generee
5. **US5:** Filtrer les tickets par statut (en attente, traite, archive)
6. **US6:** Consulter l'historique des reponses pour un ticket

### En tant qu'admin, je veux...
7. **US7:** Voir les statistiques (nombre de tickets, taux d'utilisation IA, score moyen)
8. **US8:** Gerer les utilisateurs et les permissions
9. **US9:** Configurer les parametres de l'IA (mode par defaut, token max)

---

## Exigences

### Exigences Fonctionnelles
- **EF1:** L'API doit accepter du texte brut (min 20, max 5000 caracteres)
- **EF2:** La generation IA doit prendre moins de 30 secondes
- **EF3:** La reponse IA doit contenir les champs: hook, body_points, score, hashtags, tone_justification
- **EF4:** Le statut d'un ticket peut etre: pending, processing, completed, failed
- **EF5:** La reponse proposee peut etre: draft, approved, rejected, modified
- **EF6:** L'authentification est obligatoire pour toutes les routes sauf /api/health

### Exigences Non-Fonctionnelles
- **ENF1:** Performance - Reponse API < 200ms (hors generation IA)
- **ENF2:** Disponibilite - 99.5% en production
- **ENF3:** Securite - HTTPS, JWT tokens expirees apres 24h
- **ENF4:** Scalabilite - Supporter 100+ requetes simultanees
- **ENF5:** Observabilite - Logs structures, metriques de base
- **ENF6:** Documentation - OpenAPI/Swagger pour tous les endpoints

---

## Perimetre du Projet

### Ce que l'application FAIT
- API RESTful complete pour la gestion des tickets
- Integration IA pour la generation de reponses
- Authentification et authorisation
- Tests automatises (unitaires et fonctionnels)
- Conteneurisation Docker
- Pipeline CI/CD
- Documentation technique et API

### Ce que l'application NE FAIT PAS
- Frontend SPA (React/Vue) - utilise Blade uniquement
- Systeme de paiement ou facturation
- Integration email/SMS pour notifications
- Systeme de ticketing complexe (files d'attente, escalade)
- Multi-tenant (une seule instance par deployement)
- Chat en temps reel (WebSocket)

---

## Architecture Technique

### Diagramme de Flux
```
[Client] --> [API Laravel] --> [GhostwriterService] --> [Groq API]
    |              |                    |
    |              v                    v
    |         [SQLite/MySQL]      [Queue Job]
    |              |                    |
    v              v                    v
[Blade View]  [Eloquent]          [Redis/DB Queue]
```

### Modeles de Donnees (MCD/MLD)
```
USER (id, name, email, password, created_at, updated_at)
    |
    |-- 1:N --> POST (id, user_id, text_brut_id, hook_propose, body_points,
    |                 technical_readability_score, suggested_hashtags,
    |                 tone_compliance_justification, payload_brut,
    |                 status, created_at, updated_at)
    |
    |-- 1:N --> GHOSTWRITER_JOB (id, user_id, post_id, prompt,
                                 response_raw, response_structured,
                                 status, token_usage, processing_time,
                                 error_message, created_at, updated_at)
```

### Choix Techniques
| Aspect | Choix | Justification |
|--------|-------|---------------|
| Framework | Laravel 13.8 | Ecosystem mature, documentation excellente |
| PHP | 8.3+ | Attributs, types, performance ameliorees |
| BDD (test) | SQLite :memory: | Rapide, isole, pas de config externe |
| BDD (prod) | MySQL 8.0 | Fiable, scalable, bien supporte par Laravel |
| Auth | Sanctum | Simple, API tokens, support SPA |
| Tests | Pest | Syntaxe expressive, rapide, moderne |
| IA | Groq API | OpenAI-compatible, gratuit pour le dev |
| Queue | Database | Simple, pas de Redis necessaire en dev |
| Docker | PHP 8.3 CLI | Leger, suffisant pour l'API |
| CI | GitHub Actions | Integre nativement, gratuit pour les PRs |

---

## Evaluation Criteria

### Modalites d'evaluation (60 minutes)
1. **Demonstration (10 min):** L'application fonctionne (en ligne si possible).
2. **Presentation du code (15 min):** Architecture, choix techniques, la brique IA, les tests. Tu expliques ton code, tu ne le lis pas.
3. **Mise en situation (15 min):** Resolution d'un cas pratique en direct.
4. **Code review et questions (15 min):** Questions sur le code, les tests, le deploiement, et l'usage de l'IA.

### Livrables
1. **Repository GitHub** contenant :
   - README : presentation, installation, lancement des tests, URL en ligne.
   - Les 3 diagrammes en image (PNG/JPEG) : MCD, MLD, et le schema d'architecture.
   - Le Dockerfile et le docker-compose.
   - Le workflow CI dans `.github/workflows/`.
   - Tout le code source.
2. **Lien Jira :** Le board de gestion des taches, a jour.
3. **Documentation de l'API :** Liste des endpoints (Scribe, Swagger, ou equivalent).
4. **Support de soutenance :** PDF, PPT ou Canva.
5. **(Si deploye) L'URL de l'application en ligne.**

### Criteria de performance
- **Qualite du code:** Clair, organise, bien nomme. Tu sais expliquer chaque partie.
- **Fonctionnel:** L'application fait ce qu'annonce le cahier des charges, de facon fiable.
- **L'IA apporte une vraie valeur:** Elle n'est pas un gadget.
- **Tests:** Les fonctionnalites principales sont couvertes, les tests passent.
- **Docker:** L'application demarre proprement en conteneur.
- **CI:** Le check est vert sur GitHub a chaque push.
- **Deploiement:** L'application repond en ligne (URL publique).
- **Git:** Des commits clairs et reguliers, un historique coherent.
- **Documentation:** README et doc d'API clairs et a jour.
- **Comprehension (le plus important):** Tu expliques ton code, tes choix, et ton usage de l'IA. Un projet qui marche mais que tu ne sais pas expliquer n'est pas valide.

---

## Common Commands

```bash
# Install dependencies
composer install
npm install

# Run the application
php artisan serve
npm run dev

# Run all dev services (server, queue, logs, vite)
composer dev

# Run tests
composer test
# or
php artisan test

# Run a specific test file
php artisan test tests/Feature/PostTest.php
php artisan test tests/Feature/GhostwriterTest.php

# Run a specific test method
php artisan test --filter="test_user_can_list_posts"

# Clear caches
php artisan config:clear
php artisan cache:clear

# Database operations
php artisan migrate
php artisan migrate:fresh --seed

# Code formatting (Laravel Pint)
./vendor/bin/pint
./vendor/bin/pint --test

# Docker
docker-compose up -d
docker-compose down
docker-compose build --no-cache
```

---

## Project Architecture

```
HelpDesk_AI/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # API controllers
│   │   ├── Requests/        # Form Request validation classes
│   │   └── Resources/       # API Resource transformers
│   ├── Models/              # Eloquent models
│   ├── Jobs/                # Queued jobs for async processing
│   ├── Services/            # Business logic / AI service wrappers
│   └── Providers/
├── config/
│   └── services.php         # Third-party service credentials (Groq, etc.)
├── database/
│   ├── factories/           # Model factories for testing
│   ├── migrations/          # Database schema migrations
│   └── seeders/
├── routes/
│   ├── api.php              # API routes (stateless, /api prefix)
│   └── web.php              # Web routes (Blade dashboard)
├── tests/
│   ├── Feature/             # HTTP endpoint tests
│   └── Unit/                # Unit tests for individual classes
├── Dockerfile
├── docker-compose.yml
├── .github/workflows/       # CI pipeline
└── AGENTS.md                # This file
```

---

## Code Conventions

### PHP / Laravel Style
- Follow PSR-12 coding standards.
- Use Laravel Pint (`./vendor/bin/pint`) before committing.
- Use PHP 8.3+ features: attributes, readonly properties, enums.
- Models use `#[Fillable]` and `#[Hidden]` attributes (Laravel 13 style).
- Use Form Requests for validation, never validate in controllers.
- Use API Resources to transform model data for JSON responses.
- Use `route()` helper for URL generation, never hardcode URLs.

### Naming Conventions
- Models: `SingularPascalCase` (e.g., `Post`, `GhostwriterJob`)
- Controllers: `PascalCaseController` (e.g., `PostController`)
- Migrations: `snake_case` with timestamp prefix
- Tables: `snake_case_plural` (e.g., `posts`, `ghostwriter_jobs`)
- Foreign keys: `{model}_id` (e.g., `user_id`, `text_brut_id`)
- Routes: `kebab-case` for URLs (e.g., `/api/ghostwriter/generate`)

### Testing Style
- Use Pest PHP syntax (not raw PHPUnit classes).
- Test files end with `Test.php` (e.g., `PostTest.php`).
- Test methods use `snake_case` descriptive names.
- Group related tests using `test()` or `it()`.
- Fake external services (AI, Queue, Mail) in tests.
- Use SQLite `:memory:` for test database (configured in `phpunit.xml`).

### Git Conventions
- Commit messages: imperative mood, lowercase, max 72 chars.
  - `feat: add post listing endpoint`
  - `fix: resolve unique constraint on text_brut_id`
  - `test: add GhostwriterTest with faked AI service`
- One logical change per commit.
- Never commit `.env`, `vendor/`, `node_modules/`, or `database.sqlite`.

---

## AI Integration (Groq API)

### Configuration
- AI credentials go in `.env` (never committed):
  ```
  GROQ_API_KEY=gsk_...
  GROQ_API_MODEL=llama3-8b-8192
  ```
- Reference via `config('services.groq.key')`.
- Add Groq config to `config/services.php`:
  ```php
  'groq' => [
      'key' => env('GROQ_API_KEY'),
      'model' => env('GROQ_API_MODEL', 'llama3-8b-8192'),
  ],
  ```

### SSL Certificate Fix (cURL Error 60)
The Groq API endpoint (`https://api.groq.com/openai/v1/chat/completions`) may throw `cURL error 60: SSL certificate problem` on Windows/XAMPP environments. Solutions:

1. **Download cacert.pem** from https://curl.se/ca/cacert.pem
2. Place it at a known path (e.g., `storage/cacert.pem` or `C:\xampp\php\extras\ssl\cacert.pem`)
3. In `php.ini`, set:
   ```ini
   curl.cainfo = "C:\path\to\cacert.pem"
   ```
4. Or in your AI service client, set the option:
   ```php
   Curl::create()->withOptions([
       CURLOPT_SSL_VERIFYPEER => true,
       CURLOPT_CAINFO => base_path('storage/cacert.pem'),
   ]);
   ```
5. **Never disable SSL verification** (`CURLOPT_SSL_VERIFYPEER => false`) in production.

### AI Service Pattern
- Create a service class `App\Services\GhostwriterService` that wraps the Groq API call.
- Use `Prism` or raw HTTP client (`Http::`) to call the API.
- Always use **structured output** (JSON schema) to guarantee response shape.
- Cast AI responses using Laravel casts on the model.
- Handle failures gracefully with try/catch and meaningful error messages.

### Testing AI Integration
- **Never call real AI APIs in tests.** Use one of:
  - `Http::fake()` to stub the API response.
  - A dedicated interface/contract that can be mocked.
  - `Queue::fake()` when AI calls happen via jobs.
- Define fake responses that match the real API structure.
- Example:
  ```php
  Http::fake([
      'api.groq.com/*' => Http::response([
          'choices' => [
              ['message' => ['content' => '{"hook":"...","body":"..."}']]
          ],
      ], 200),
  ]);
  ```

---

## Database

### Testing Database
- Tests use SQLite `:memory:` (configured in `phpunit.xml`).
- Each test runs in a transaction that rolls back automatically.
- Do NOT use `RefreshDatabase` trait unless explicitly needed (it's slow).
- Use `DatabaseMigrations` trait if migrations are required per-test.

### Unique Constraints
- The `posts.text_brut_id` column has a UNIQUE constraint.
- When seeding test data, ensure `text_brut_id` values are unique per record.
- Use `fake()->unique()` in factories:
  ```php
  'text_brut_id' => $fake->unique()->numberBetween(1, 10000),
  ```
- Or use sequential IDs in test data to avoid collisions.

### Migration Best Practices
- Always write `down()` method for rollback.
- Use `foreignId()->constrained()` for foreign keys.
- Add indexes on columns used in `WHERE`, `ORDER BY`, and `JOIN`.
- Use `nullable()` for optional fields, never leave them undefined.

---

## API Design

### Route Structure (routes/api.php)
```
GET    /api/posts              → PostController@index      (list all posts)
POST   /api/posts              → PostController@store      (create post)
GET    /api/posts/{id}         → PostController@show       (get single post)
PUT    /api/posts/{id}         → PostController@update     (update post)
DELETE /api/posts/{id}         → PostController@destroy    (delete post)

POST   /api/ghostwriter/generate  → GhostwriterController@generate  (AI generation)
GET    /api/ghostwriter/{id}      → GhostwriterController@show      (check status)
```

### HTTP Status Codes
- `200` OK - Successful read/update
- `201` Created - Successful creation
- `202` Accepted - Job queued (async processing)
- `400` Bad Request - Invalid input
- `401` Unauthorized - Missing/invalid authentication
- `403` Forbidden - Insufficient permissions
- `404` Not Found - Resource doesn't exist
- `422` Unprocessable Entity - Validation errors
- `500` Internal Server Error - Server failure

### Response Format
Use API Resources for consistent JSON structure:
```json
{
    "data": {
        "id": 1,
        "text_brut_id": 1,
        "hook_propose": "...",
        "status": "draft",
        "created_at": "2026-07-15T10:00:00Z"
    },
    "meta": {},
    "links": {}
}
```

### Authentication
- Use Laravel Sanctum for token-based authentication (Bearer tokens).
- Protected routes use `auth:sanctum` middleware.
- Public endpoints (if any) are explicitly defined without middleware.

---

## Testing Guidelines

### Test Structure (tests/Feature/)
```php
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    // Test that the endpoint returns 200
    public function test_user_can_list_posts(): void
    {
        $posts = Post::factory()->count(3)->create();

        $response = $this->getJson('/api/posts');

        $response->assertOk()
                 ->assertJsonCount(3, 'data');
    }

    // Test authentication
    public function test_unauthenticated_user_cannot_create_post(): void
    {
        $response = $this->postJson('/api/posts', []);

        $response->assertUnauthorized();
    }

    // Test validation
    public function test_create_post_requires_fields(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
                          ->postJson('/api/posts', []);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['text_brut_id']);
    }
}
```

### What to Test
1. **Happy paths** - Endpoints return correct status + structure
2. **Authentication** - Protected routes return 401 without token
3. **Validation** - Required fields enforced (422 errors)
4. **Authorization** - Users can't modify others' resources
5. **Queue dispatch** - Jobs are dispatched when expected (`Queue::fake()`)
6. **Edge cases** - Empty states, max lengths, special characters

### What NOT to Test
- Third-party service internals (Groq API, email delivery)
- Framework behavior (Eloquent queries, routing)
- Blade views (use separate browser tests if needed)

---

## Docker

### Dockerfile
```dockerfile
FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libssl-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN php artisan key:generate
RUN php artisan config:cache

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

### docker-compose.yml
```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8000:8000"
    volumes:
      - .:/app
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_CONNECTION=mysql
      - DB_HOST=db
      - DB_PORT=3306
      - DB_DATABASE=helpdesk
      - DB_USERNAME=root
      - DB_PASSWORD=secret
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: helpdesk
      MYSQL_ROOT_PASSWORD: secret
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

---

## CI/CD (GitHub Actions)

### Workflow File (.github/workflows/ci.yml)
```yaml
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: testing
          MYSQL_ROOT_PASSWORD: password
        ports:
          - 3306:3306

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, ctype, json, bcmath, pdo_mysql

      - name: Install Dependencies
        run: composer install --no-progress

      - name: Prepare Environment
        run: |
          cp .env.example .env
          php artisan key:generate

      - name: Run Migrations
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: testing
          DB_USERNAME: root
          DB_PASSWORD: password
        run: php artisan migrate --force

      - name: Run Tests
        run: php artisan test

      - name: Check Code Style
        run: ./vendor/bin/pint --test
```

### CI Requirements
- All tests must pass before merge.
- Pint formatting check must be green.
- Never skip CI checks or force-push to main.

---

## Deployment

### Environment Variables (Production)
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://your-app-url.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=helpdesk
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

GROQ_API_KEY=gsk_...
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Pre-Deployment Checklist
- [ ] `APP_DEBUG=false` in production `.env`
- [ ] `.env` is NOT committed to git
- [ ] All tests pass (`composer test`)
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] HTTPS configured on hosting platform
- [ ] Database migrations run on production

### Supported Platforms
- **Primary:** Railway, Render, or Fly.io (free tier)
- **Alternative:** Azure App Service
- **Requirements:** PHP 8.3+, MySQL, Composer, Node.js (for asset build)

---

## Troubleshooting

### cURL Error 60 (SSL Certificate)
**Symptom:** `cURL error 60: SSL certificate problem: unable to get local issuer certificate`
**Cause:** Missing or outdated CA certificate bundle on Windows/XAMPP.
**Fix:** See "SSL Certificate Fix" section above. Download `cacert.pem` and configure `curl.cainfo` in `php.ini`.

### Unique Constraint Violation
**Symptom:** `SQLSTATE[23000]: Integrity constraint violation: UNIQUE constraint failed: posts.text_brut_id`
**Cause:** Inserting duplicate `text_brut_id` values (test data collision).
**Fix:** Use `fake()->unique()->numberBetween(...)` in factories. Reset the unique resolver between tests if needed, or use sequential values.

### SQLite :memory: vs File
**Symptom:** Tests share state or fail unexpectedly.
**Cause:** SQLite `:memory:` is fresh per process; file-based SQLite persists.
**Fix:** Ensure `phpunit.xml` has `DB_DATABASE=:memory:`. Each test process gets a clean database.

### Queue Jobs Not Processing
**Symptom:** Jobs dispatched but never executed.
**Cause:** Queue worker not running, or `QUEUE_CONNECTION=sync` in tests.
**Fix:** Run `php artisan queue:listen` in dev. In tests, use `Queue::fake()` to assert dispatch without processing.

### AI Service Timeout
**Symptom:** `cURL error 28: Operation timed out`
**Cause:** Slow AI response or network issues.
**Fix:** Set appropriate timeout on HTTP client:
```php
Http::timeout(30)->post($url, $payload);
```
Also consider using a Job with retry logic for production.

---

## File Reference

| File | Purpose |
|------|---------|
| `AGENTS.md` | This file - project conventions and guidelines |
| `README.md` | User-facing documentation (install, usage, URL) |
| `.env.example` | Environment variable template |
| `phpunit.xml` | PHPUnit/Pest configuration (SQLite :memory:) |
| `composer.json` | PHP dependencies and scripts |
| `Dockerfile` | Docker container definition |
| `docker-compose.yml` | Multi-service Docker orchestration |
| `.github/workflows/ci.yml` | CI pipeline definition |
| `routes/api.php` | API route definitions |
| `routes/web.php` | Web/Blade route definitions |
| `config/services.php` | Third-party service credentials |
| `app/Models/` | Eloquent models |
| `app/Http/Controllers/` | Request handlers |
| `app/Http/Requests/` | Form validation classes |
| `app/Http/Resources/` | API response transformers |
| `app/Jobs/` | Queued background jobs |
| `app/Services/` | Business logic (AI wrapper, etc.) |
| `database/migrations/` | Database schema definitions |
| `database/factories/` | Test data generators |
| `tests/Feature/` | HTTP endpoint tests |
| `tests/Unit/` | Unit tests |

---

## Phase Checklist (Project Timeline)

- [ ] **Phase 1:** Cadrage - User stories, scope definition, README
- [ ] **Phase 2:** Jira - Task board, sprints, kept up to date
- [ ] **Phase 3:** Conception - MCD, MLD diagrams, architecture schema
- [ ] **Phase 4:** Laravel Dev - API routes, controllers, validation, resources, auth, queues, Blade front
- [ ] **Phase 5:** AI Integration - Groq API, structured output, casts, real value added
- [ ] **Phase 6:** Tests - Feature tests (200/401/422), Queue::fake, Http::fake
- [ ] **Phase 7:** Docker - Dockerfile + docker-compose, works on any machine
- [ ] **Phase 8:** CI - GitHub Actions, green check on every push
- [ ] **Phase 9:** Deployment - Live URL, APP_DEBUG=false, .env secured
- [ ] **Phase 10:** Documentation - README, API docs (Scribe/Swagger), all diagrams

### Bonus
- [ ] CD (continuous deployment on push)
- [ ] Docker image build in CI + deploy image
- [ ] Monitoring/observability in production
- [ ] Laravel Pint check in CI
