# Ignis — Sistema de Inscrições (Laravel + Livewire)

![Prévia da página inicial](docs/preview-home.svg)

> **Por que "Ignis"?** É a palavra latina para *fogo* — mesma raiz de "ignição" e "incendiar".
> Não por acaso: no imaginário cristão o fogo é símbolo do Espírito Santo e do entusiasmo da fé
> vivida com intensidade, exatamente o espírito que o acampamento busca acender em cada jovem. 🔥

Aplicação **monólito Laravel** (Blade + Livewire 3, PostgreSQL) para o sistema de inscrições do
5º Acampamento de Jovens Ignis. Não há front-end separado: a interface pública e o painel
administrativo são renderizados pelo próprio Laravel, reaproveitando o design (paleta navy/gold,
tipografia Montserrat) do protótipo React/Base44 original (`ignis-front/`), portado quase
literalmente via Tailwind.

Gerado a partir de:
- `dump-laravel-*.sql` (schema existente) e `Guia_Migracao_Laravel_Ignis.pdf` (mapeamento
  Base44 → Laravel).
- `baseData/entities/*.jsonc` e `src/pages/*.jsx` do protótipo front (`ignis-front/`), usados como
  fonte de verdade para regras de negócio, copy e layout de cada tela.

## Versões utilizadas

| Ferramenta | Versão | Onde é fixada |
|---|---|---|
| PHP | 8.3 (mínimo exigido: 8.2+) | `Dockerfile` (`php:8.3-cli`) / `composer.json` (`"php": "^8.2"`) |
| Laravel Framework | 12.64.0 | `composer.json` (`^12.0`) |
| Livewire | 3.8.2 | `composer.json` (`^3.5`) |
| Maatwebsite/Laravel-Excel | 3.1.69 | `composer.json` (`^3.1`) |
| PostgreSQL | 16.14 | `docker-compose.yml` (`postgres:16-alpine`) |
| Redis | 7 | `docker-compose.yml` (`redis:7-alpine`) |
| Node.js | 20 | `docker-compose.yml` (`node:20-alpine`, usado só para build de assets) |
| Vite | 6.4.3 | `package.json` (`^6.0.0`) |
| Tailwind CSS | 3.4.19 | `package.json` (`^3.4.17`) |
| Chart.js | 4.5.1 | `package.json` (`^4.4.4`) |
| Mailpit | latest | `docker-compose.yml` — só para inspecionar e-mails em desenvolvimento |

## Como rodar

### Docker (recomendado — atende ao requisito de "ambiente containerizado")

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose run --rm node   # build dos assets (Tailwind/Vite) para produção
```
App em `http://localhost:8000`, e-mails de teste em `http://localhost:8025` (Mailpit).

### Local (PHP 8.2+, Postgres à parte)

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run dev   # em outro terminal, para HMR do Tailwind
php artisan serve
```

O `AdminUserSeeder` cria o admin inicial com `ADMIN_SEED_EMAIL`/`ADMIN_SEED_PASSWORD` do `.env`
(padrão: `admin@ignis.local` / `troque-esta-senha`) — troque antes de subir para produção.

## Arquitetura

- **Blade + Livewire 3** em vez de SPA: toda tela com estado (formulários, filtros, upload,
  modais, toggles) é um componente Livewire full-page (`app/Livewire/**`, roteado diretamente em
  `routes/web.php` como `Route::get(..., \App\Livewire\X::class)`). Páginas somente-leitura
  (`/`, `/equipe`) são controllers + Blade simples (`PublicSiteController`).
- **Auth por sessão** (guard `web`, `Auth::attempt` em `App\Livewire\Auth\Login`) — não há mais
  API tokenizada/Sanctum; o painel admin é protegido pelos middlewares `auth` + `admin`
  (`App\Http\Middleware\EnsureUserIsAdmin`, alias registrado em `bootstrap/app.php`).
- **Domínio inalterado**: Models, `database/migrations`, `database/seeders` e os Services
  (`RegistrationService`, `VagasLimiteService`, `FileUploadService`) são os mesmos usados desde a
  versão em API — os componentes Livewire chamam esses Services diretamente, sem camada de
  controller JSON no meio.
- **Design system portado de `ignis-front`**: `tailwind.config.js` e as CSS vars `--ignis-*`
  (`resources/css/app.css`) são praticamente idênticos aos do protótipo React. Ícones são um
  componente Blade local (`<x-icon name="..."/>`, `resources/views/components/icon.blade.php`)
  com os paths SVG do Lucide usados no front original — sem dependência externa. Gráficos do
  relatório admin usam Chart.js (`resources/js/app.js`) no lugar do `recharts` original.

## Decisões importantes (schema)

- **Migrations completas em vez de import do dump**: todas as tabelas do dump foram
  recriadas como migrations do zero (não apenas os campos novos), para que o projeto
  seja reproduzível via `php artisan migrate` sem depender de um `.sql` externo.
- **Campos novos** adicionados conforme o guia de migração: `inscritos.termos_aceitos` (jsonb),
  `contatos_inscrito.nome_emergencia`/`telefone_emergencia`, `responsaveis_inscrito.documento_url`,
  e em `evento_inscritos`: `metodo_pagamento`, `comprovante_pagamento_url`, `parcelas_pagamento`,
  `observacoes`. (O formulário não coleta mais foto do participante — `inscritos` não tem coluna
  de foto.)
- **`media_items` e `team_members` são obrigatórias**, não opcionais — as telas `admin/midias` e
  `admin/equipe` consomem essas entidades ativamente.
- **Idade nunca é armazenada**: é um accessor calculado (`$inscrito->idade`) a partir de
  `data_nascimento`.
- **`termos_aceitos`** substitui os 5 booleanos soltos do Base44 (`term_truth`,
  `term_participation`, `term_emergency`, `term_image`, `term_rules`) por um único campo `jsonb`.
- **`Setting::DEFAULTS`** replica o objeto `DEFAULTS` que existia em `settingsHelper.jsx` do
  protótipo front, para manter os mesmos textos/valores padrão do site.
- Constraints `CHECK` (`chk_data_evento`, `chk_limite_vagas`) foram replicadas via `DB::statement`,
  pois o Schema Builder do Laravel não tem uma API fluente e portável para isso.

## Discos de armazenamento (`config/filesystems.php`)

- **`public`**: mídias e fotos da equipe — arquivos não sensíveis, servidos via URL pública
  (`php artisan storage:link`).
- **`local`** (root `storage/app/private`): `documento_url` (responsável) e
  `comprovante_pagamento_url` (pagamento) — arquivos sensíveis, **nunca** expostos por URL
  pública. Servidos apenas via `GET /admin/files?path=...`, protegida pelos middlewares
  `auth` + `admin` (`App\Http\Controllers\Admin\FileController`).

## Fila de e-mails

Os `Mail::...->queue(...)` em `RegistrationService`/`VagasLimiteService` exigem
`QUEUE_CONNECTION` configurado (padrão `database`; `redis` disponível via Docker) e um worker
rodando (`php artisan queue:work`).

## Regras de negócio implementadas

- **Menor de idade**: `App\Livewire\Inscricao` exige `guardian_name` e `guardianDocument` quando a
  idade calculada a partir de `birth_date` é menor que 18 (recalculada a cada mudança de data via
  `wire:model.live`).
- **Idade mínima do evento**: validada dinamicamente contra `eventos.idade_minima` (seed atual:
  13 anos, conforme especificação funcional).
- **Limite de vagas**: `RegistrationService::register()` rejeita novas inscrições
  (`VagasEsgotadasException`) quando `Evento::atingiuLimiteVagas()`; o formulário público também
  bloqueia a tela inteira preventivamente (`isFull`) quando o evento já bateu o limite ou
  `settings.registration_open = false`. `VagasLimiteService::verificarLimite()` fecha as
  inscrições e envia o `.xlsx` ao coordenador assim que uma confirmação atinge o limite —
  chamado a partir de `RegistrationService::confirmarPagamento()` (usado tanto pelo fluxo
  público de pagamento quanto pela confirmação manual no painel admin).
- **E-mail único por inscrição**: índice único parcial em `contatos_inscrito.email` (nível banco)
  + `Rule::unique` em `Inscricao::rules()` (nível aplicação, com mensagem amigável) impedem a
  mesma pessoa de se inscrever duas vezes com o mesmo e-mail.
- **Autoatendimento (`/minha-inscricao`)**: o próprio inscrito pode alterar seus dados (exceto
  e-mail e campos de pagamento/status) até a véspera do evento — `MinhaInscricao::canEdit()`
  bloqueia a edição quando `evento.data_inicio` já passou ou a inscrição está
  cancelada/recusada. Sem autenticação: o acesso é só pelo e-mail, igual à busca de status.
- **Pagamento**: PIX exige comprovante (armazenado no disco privado); cartão aceita 1x ou 2x via API Efí (tokenização no browser + cobrança one-step).
- **E-mails automáticos**: `InscricaoRecebidaMail` (ao registrar), `InscricaoConfirmadaMail` (ao
  confirmar pagamento) e `LimiteVagasAtingidoMail` (ao coordenador, com o `.xlsx` em anexo).

## Rotas principais

Públicas: `/` (Home), `/inscricao`, `/pagamento/{eventoInscrito}`, `/minha-inscricao`, `/equipe`,
`/login`.

Admin (`auth` + `admin`, prefixo `/admin`): `/` (dashboard), `/inscricoes`, `/configuracoes`,
`/midias`, `/equipe`, `/relatorios`, `/reports/export-csv`, `/files`.

## Pagamento com cartão (Efí Bank)

Integração com a API Cobranças > Cartão da Efí. Para testar em homologação, siga o guia passo a passo em [`docs/README-teste-cobranca-cartao.md`](docs/README-teste-cobranca-cartao.md).

## Próximos passos sugeridos

1. Testes automatizados (Feature tests com Livewire's testing helpers) para o fluxo completo de
   inscrição, pagamento, confirmação e bloqueio de vagas.
2. Policies/Gates mais granulares, se houver mais de um nível de permissão admin no futuro.
3. Self-host dos assets de marca (logo, brasão, fotos padrão da galeria) hoje ainda apontando para
   o CDN do Base44 (`PublicSiteController`) — opcional, hoje funcional mas é uma dependência
   externa que pode ser eliminada.
