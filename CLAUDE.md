# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repository is

A full, runnable **Laravel monolith** (Blade + Livewire 3, PostgreSQL) — no separate frontend.
It was originally scaffolded as a bare `app/`+`database/` package (no `composer.json`/`artisan`/
`config/`) meant to be dropped into a separate React SPA + Laravel JSON API pair; it has since been
converted into a self-contained server-rendered app. There is a sibling repo, `ignis-front/`
(not this directory), which was the original React/Vite/Base44 prototype — it is **no longer
consumed at runtime**, but its `src/pages/*.jsx`, `src/index.css`, and `tailwind.config.js` are the
design source of truth this app's Blade views were ported from (matching visuals/copy exactly was
an explicit requirement). When in doubt about intended UI behavior for a page, the corresponding
`ignis-front/src/pages/*.jsx` file (if still present alongside this repo) is the reference — but
this repo's Livewire component is the implementation to modify, never `ignis-front`.

- Commands: `composer install`, `npm install`, `php artisan migrate --seed`,
  `php artisan serve`, `npm run dev` (Vite/Tailwind HMR), `npm run build`. Or via Docker:
  `docker compose up -d --build` then `docker compose exec app php artisan migrate --seed`.
- No automated test suite exists yet (flagged as a suggested next step in `README.md`) — verify
  changes by running the app and clicking through the affected flow.
- Requires PHP 8.2+; PostgreSQL only (see "Conventions to preserve").
- `README.md` documents *why* each non-obvious schema/business-rule decision was made — read it
  before making structural changes.

## Domain context

This is the registration system for **Ignis**, a youth camping event ("Acampamento de Jovens")
for a parish (Paróquia São Pedro Apóstolo). It was migrated from a Base44 low-code backend to
Laravel (source docs: `dump-laravel-*.sql`, `Guia_Migracao_Laravel_Ignis.pdf`, front-end
`baseData/entities/*.jsonc` — referenced in README, not included in this repo), then converted a
second time from "Laravel JSON API + separate React SPA" into this single Blade+Livewire monolith.

Portuguese is the language of the domain model (table/column/class names) and user-facing
strings (validation messages, emails, UI copy); code identifiers in English (e.g.
`RegistrationService`) are used where the concept is generic/framework-facing.

## Architecture

**Entities and relationships** (`app/Models`, `database/migrations`) — unchanged by the
monolith conversion:

- `Evento` (event) — the camp. The system assumes **a single active event at a time**;
  `Evento::atual()` returns the most recent `ativo=true` event by `data_inicio`. Vacancy
  logic lives on the model: `totalConfirmadas()`, `vagasDisponiveis()`, `atingiuLimiteVagas()`.
  Seeded by `database/seeders/EventoSeeder.php` (150 vagas, idade mínima 13, R$150 — per the
  functional spec, not the migration column defaults which differ slightly).
- `Inscrito` (registrant/participant) — personal data. Has one `ContatoInscrito` (contact),
  one `InformacaoSaude` (health info), one `ResponsavelInscrito` (guardian, only present
  for minors), and many `EventoInscrito` through a belongsToMany to `Evento`.
  - **Age (`idade`) is never stored** — it's an `Attribute` accessor computed from
    `data_nascimento` on every access. Don't add an `idade`/`age` column.
  - `termos_aceitos` is a single `jsonb` column (cast to `array`) holding the five consent
    checkboxes (`term_truth`, `term_participation`, `term_emergency`, `term_image`,
    `term_rules`).
- `EventoInscrito` (pivot/junction with its own identity, not a plain pivot table) — one
  registrant's registration to one event: `status_id`, `numero_inscricao`, payment fields
  (`pagamento_confirmado`, `metodo_pagamento`, `comprovante_pagamento_url`,
  `parcelas_pagamento`), `observacoes`, `presente`.
- `StatusInscricao` — registration status lookup table; use the class constants
  (`StatusInscricao::PENDENTE`, `AGUARDANDO_PAGAMENTO`, `CONFIRMADA`, `CANCELADA`,
  `RECUSADA`, `LISTA_ESPERA`), never magic strings.
- `Setting` — key/value store for site config. `Setting::DEFAULTS` mirrors what used to be
  `settingsHelper.jsx`'s `DEFAULTS` in the old front-end prototype. `Setting::allAsArray()`
  merges stored rows over defaults; `Setting::set()` invalidates the `settings.all` cache key.
- `MediaItem`, `TeamMember` — content for the public site's media gallery and team page;
  required entities, actively managed from `/admin/midias` and `/admin/equipe`.

**Request flow for registration** (the core business flow) — same `RegistrationService`/
`VagasLimiteService` as before, now called directly from Livewire components instead of JSON
controllers:

1. `GET/POST /inscricao` → `App\Livewire\Inscricao`. Its `rules()` mirror what used to live in
   `StoreRegistrationRequest`: guardian fields (`guardian_name`, `guardianDocument`) become
   required only when the live-computed age (`updatedBirthDate()`, fired by
   `wire:model.live="birth_date"`) is under 18; minimum age is checked against the *active
   event's* `idade_minima`; the whole form is preemptively hidden behind an "inscrições
   encerradas" screen (`$isFull`, set in `mount()`) if no active event exists or the vacancy
   limit/`registration_open` flag already blocks new signups.
2. `Inscricao::submit()` builds the `$data` array `RegistrationService::register()` expects
   (see that method's body for the exact keys — it's unforgiving about key names) and calls it
   inside a `DB::transaction`: creates `Inscrito` + `ContatoInscrito` + `InformacaoSaude`
   (+ `ResponsavelInscrito` with a privately-stored guardian document if a minor), creates the
   `EventoInscrito` row with `numero_inscricao`
   (`INS` + zero-padded id) and `PENDENTE` status, then queues `InscricaoRecebidaMail`. On
   success it redirects to `/pagamento/{eventoInscrito}`.
3. `GET/POST /pagamento/{eventoInscrito}` → `App\Livewire\Pagamento` (route-model-bound). PIX
   requires a proof upload; card accepts 1 or 2 installments. `submit()` updates the
   `EventoInscrito` payment fields directly (no intermediate service method — this logic used to
   live in a controller, now lives in the Livewire component) and flips local `$done` state to
   show the confirmation screen in place (no redirect, matching the original SPA UX).
4. Payment **confirmation** (marking `CONFIRMADA`) only happens from the admin side —
   `App\Livewire\Admin\Inscricoes::confirm()` calls `RegistrationService::confirmarPagamento()`,
   which sets status to `CONFIRMADA`, queues `InscricaoConfirmadaMail`, then calls
   `VagasLimiteService::verificarLimite()`.
5. `VagasLimiteService::verificarLimite()` is the vacancy-limit side effect: once confirmed
   registrations hit `limite_vagas`, it flips `settings.registration_open` to `false`
   (idempotent — checks the flag first to avoid duplicate sends), generates an `.xlsx` via
   `ParticipantesExport` (Maatwebsite/Laravel-Excel), and emails it to
   `settings.coordinator_email` via `LimiteVagasAtingidoMail`. If no coordinator email is
   configured, it silently skips the send.
6. `GET /minha-inscricao` → `App\Livewire\MinhaInscricao` (linked from the home hero, "Já se
   inscreveu? Acessar minha inscrição"). Unauthenticated self-service, keyed by e-mail (no
   token/OTP confirmation — same trust model as the original search-only page). Besides
   search/status display, it lets the registrant edit their own data via
   `MinhaInscricao::edit()`/`saveEdit()`, gated by `canEdit()`: blocked once
   `status` is `CANCELADA`/`RECUSADA`, and once `evento.data_inicio` is no longer in the future
   (the only concept of a registration deadline in this system — there's no separate
   `data_limite_inscricao` field). Persists via `RegistrationService::atualizarDados()`, which
   mirrors `register()`'s shape but never touches `email` (the lookup key) or payment/status
   fields, and only replaces the guardian document if a new one is uploaded.

**File storage** — two disks, chosen per sensitivity (`FileUploadService`, unchanged):
- `public` disk (root `storage/app/public`): media items, team photos — served via public URL,
  requires `php artisan storage:link`. (Registration no longer collects a participant photo —
  `Inscrito` has no photo column.)
- `local` disk (root `storage/app/private`): guardian documents, payment proofs — **never**
  given a public URL; served only through `GET /admin/files?path=...`
  (`Admin\FileController`), which sits behind `auth` + `admin` middleware.
- Uploaded filenames are always regenerated as a UUID + original extension
  (`generateFileName`) — never trust/store the client's original filename.
- Livewire's `TemporaryUploadedFile` (from `WithFileUploads`) extends `UploadedFile`, so it's
  passed straight into `FileUploadService::storePublic()`/`storePrivate()` unchanged — no
  adapter needed between Livewire uploads and this service.

**Auth** — session-based (guard `web`, `Auth::attempt()` in `App\Livewire\Auth\Login`), **not**
Sanctum/API tokens (that was removed along with the JSON API layer — see below). Every
`/admin/*` route is gated by `['auth', 'admin']` middleware (`EnsureUserIsAdmin`, alias
registered in `bootstrap/app.php`), which `abort(403)`s non-admin users
(`role === 'admin'`; single role tier today). `resources/views/errors/403.blade.php` renders a
themed error page with a logout link for that edge case.

**No JSON API**: `routes/api.php` and the old JSON controllers (`RegistrationController`,
`Admin\AdminRegistrationController`, `Admin\AdminSettingController`, `Admin\MediaItemController`,
`Admin\TeamMemberController`, `Admin\ReportController`, `EventController`, `SettingController`,
`ShirtSizeController`, `StatusInscricaoController`) and their `FormRequest`s were deleted when
this became a monolith — their logic now lives directly in `App\Livewire\**` components and
`App\Http\Controllers\PublicSiteController`. Don't recreate a `routes/api.php` unless a real
external API consumer shows up; there isn't one today.

**Icons**: no icon package dependency — `resources/views/components/icon.blade.php` inlines the
~25 Lucide SVG paths actually used (`<x-icon name="calendar" class="w-4 h-4" />`). Add new icons
there rather than pulling in a package.

**Charts**: `admin/relatorios` (`App\Livewire\Admin\Relatorios`) computes all chart data in PHP
and renders with Chart.js (`resources/js/app.js` exposes it as `window.Chart`), instantiated
inline in the Blade view inside a Livewire `@script`/`@endscript` block (not a plain `<script>`
tag — that's what makes it re-run safely on Livewire re-renders instead of double-instantiating).

## Conventions to preserve

- Portuguese table/column names (`inscritos`, `eventos`, `evento_inscritos`,
  `tamanhos_camisa`, `status_inscricao`, `contatos_inscrito`, `informacoes_saude`,
  `responsaveis_inscrito`) but English keys where they map to a pre-existing contract
  (e.g. `full_name`, `birth_date`, `shirt_size` as Livewire component property names,
  inherited from the original front-end's field names).
- Postgres-specific schema features are used deliberately and via `DB::statement` where
  Laravel's Schema Builder has no portable fluent API: `jsonb` columns and `CHECK`
  constraints (`chk_data_evento`, `chk_limite_vagas`). Don't "simplify" these to
  database-agnostic equivalents — this app targets PostgreSQL only (`config/database.php`
  default connection is `pgsql`, no other driver is configured).
- All money-losing/duplicate-side-effect risks are guarded explicitly (e.g. the
  `registration_open === 'false'` early-return in `VagasLimiteService` to avoid
  re-sending the coordinator email). Preserve these guards when touching that code.
- Mail is always sent via `Mail::to(...)->queue(...)`, never `send()` — a queue worker
  (`php artisan queue:work`) is expected to be running (`QUEUE_CONNECTION=database` by default,
  `redis` available via Docker).
- The original React pages sometimes used inconsistent accent colors (`purple-500` in
  `Inscricao`/`Pagamento`/`AdminInscricoes`/`AdminMidias` vs. `ignis-gold` elsewhere) — this was
  preserved intentionally in the Blade port for visual fidelity, not "fixed", per an explicit
  "interface idêntica" requirement. Don't unify them without checking with the user first.
