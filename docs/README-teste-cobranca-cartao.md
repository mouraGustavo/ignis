# Teste de cobrança por cartão de crédito (Efí Bank)

Guia passo a passo para testar o fluxo de pagamento com cartão no Ignis, em ambiente de **homologação (sandbox)** da Efí.

---

## 1. Pré-requisitos na conta Efí

Antes de testar no Ignis, confirme no painel da Efí:

1. **API de Emissão de cobranças** ativada na aplicação.
2. **Ramo de atividade** cadastrado na conta.
3. Credenciais de homologação geradas:
   - `Client_Id`
   - `Client_Secret`
   - Certificado `.p12` ou `.pem`
4. **Identificador de conta** (`payee_code`) — menu `API > Introdução > Identificador de conta`.  
   Obrigatório para tokenização do cartão no navegador.
5. (Produção) Formulário **KYC** para cartão não presente preenchido e aprovado.

Documentação oficial: [API Cobranças > Cartão](https://dev.efipay.com.br/docs/api-cobrancas/cartao/)

---

## 2. Configurar o ambiente local

### 2.1 Variáveis no `.env`

Copie de `.env.example` e preencha:

```env
APP_URL=http://localhost:8000

# Efí Bank — homologação
EFI_SANDBOX=true
EFI_CLIENT_ID=Client_Id_...
EFI_CLIENT_SECRET=Client_Secret_...
EFI_CERTIFICATE_PATH=storage/app/private/efi/certificate.p12
EFI_CERTIFICATE_PASSWORD=          # senha do .p12, se houver
EFI_PAYEE_CODE=                   # Identificador de conta (obrigatório no front)
EFI_NOTIFICATION_URL=http://localhost:8000/webhooks/efi
EFI_TIMEOUT=30
EFI_CACHE=true
EFI_DEBUG=false                   # use true para depurar erros da Efí
```

| Variável | Descrição |
|----------|-----------|
| `EFI_SANDBOX` | `true` = homologação, `false` = produção |
| `EFI_PAYEE_CODE` | Sem este valor o formulário de cartão exibe erro e não tokeniza |
| `EFI_NOTIFICATION_URL` | URL que a Efí chama quando o status da cobrança muda |

### 2.2 Certificado

Coloque o arquivo da Efí em:

```
storage/app/private/efi/certificate.p12
```

O caminho deve bater com `EFI_CERTIFICATE_PATH`. **Nunca** coloque o certificado em `public/`.

### 2.3 Banco e dependências

**Com Docker (recomendado):**

```bash
docker compose up -d
docker compose exec php php artisan key:generate   # se ainda não tiver APP_KEY
docker compose exec php php artisan migrate --seed
docker compose exec php php artisan config:clear
```

**Sem Docker:**

```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan config:clear
```

A migration `2026_07_23_000001_create_payments_table` cria as tabelas `payments` e `payment_notifications`.

### 2.4 Assets do front-end

O pagamento usa a biblioteca JavaScript da Efí (CDN) + Vite/Alpine:

```bash
# Docker
docker compose exec node npm install
docker compose exec node npm run build

# Local
npm install && npm run build
```

Em desenvolvimento, o serviço `node` do `docker-compose.yml` já roda `npm run dev` na porta `5173`.

### 2.5 Fila de jobs (webhooks e e-mails)

Webhooks e e-mails de confirmação usam fila. Com `QUEUE_CONNECTION=database`:

```bash
# Docker
docker compose exec php php artisan queue:work

# Local
php artisan queue:work
```

Sem o worker, o webhook é gravado mas o job `ProcessEfiNotificationJob` não roda até você iniciar o worker.

---

## 3. Webhook em desenvolvimento local

A Efí precisa **alcançar** a URL de notificação pela internet. `http://localhost:8000` **não** é acessível de fora da sua máquina.

| Cenário | O que testar |
|---------|----------------|
| Só fluxo síncrono (resposta imediata `approved`) | `EFI_NOTIFICATION_URL=http://localhost:8000/webhooks/efi` é suficiente |
| Confirmação assíncrona via webhook (`waiting` → `paid`) | Exponha a app com [ngrok](https://ngrok.com/), Cloudflare Tunnel ou similar |

**Exemplo com ngrok:**

```bash
ngrok http 8000
# Copie a URL HTTPS, ex: https://abc123.ngrok-free.app
```

Atualize o `.env`:

```env
EFI_NOTIFICATION_URL=https://abc123.ngrok-free.app/webhooks/efi
```

Reinicie a config:

```bash
docker compose exec php php artisan config:clear
```

---

## 4. Fluxo de teste manual (passo a passo)

### Passo 1 — Abrir a inscrição

1. Acesse `http://localhost:8000/inscricao`
2. Preencha o formulário com dados válidos (idade mínima do evento: 13 anos).
3. Use um **e-mail único** (cada e-mail só pode inscrever uma vez).
4. Envie o formulário — você será redirecionado para a tela de pagamento.

### Passo 2 — Escolher cartão de crédito

1. Na URL `/pagamento/{id}`, clique em **Cartão de Crédito**.
2. Verifique se **não** aparece a mensagem *"Pagamento por cartão temporariamente indisponível"* (indica `EFI_PAYEE_CODE` vazio ou script da Efí bloqueado).
3. Preencha os dados do cartão (veja cartões de teste na seção 5).

### Passo 3 — Pagar

1. Clique em **Pagar com cartão →**
2. O navegador gera o `payment_token` (dados do cartão **não** vão para o servidor Laravel).
3. Aguarde o processamento.

### Passo 4 — Resultado esperado

| Resultado na tela | Status da inscrição | O que aconteceu |
|-------------------|---------------------|-----------------|
| **Inscrição confirmada!** | `CONFIRMADA` | Pagamento `approved` — confirmação automática + e-mail |
| **Pagamento em processamento** | `AGUARDANDO_PAGAMENTO` | Status `waiting` — aguarda webhook ou consulta posterior |
| Mensagem de erro (cartão recusado) | `PENDENTE` | Pode tentar novamente com outro cartão |

### Passo 5 — Conferir no admin

1. Acesse `http://localhost:8000/login`  
   Credenciais padrão (seeder): `admin@ignis.local` / `troque-esta-senha`
2. Vá em **Admin → Inscrições**
3. Abra os detalhes da inscrição e verifique:
   - **Status pagamento** (Pago, Recusado, Autorizado, etc.)
   - **ID cobrança Efí** (`charge_id`)
   - **Cartão** (máscara, ex: `XXXXXXXXXXXX3991`)
   - **Motivo recusa** (se houver)

### Passo 6 — Conferir no banco (opcional)

```bash
docker compose exec php php artisan tinker
```

```php
\App\Models\Payment::latest()->first();
\App\Models\PaymentNotification::latest()->first();
```

---

## 5. Cartões de teste (homologação Efí)

Na sandbox, o resultado depende do **último dígito** do número do cartão:

| Final do cartão | Resultado |
|-----------------|-----------|
| **1** | `"Dados do cartão inválidos."` |
| **2** | `"Transação não autorizada por motivos de segurança."` |
| **3** | `"Transação não autorizada, tente novamente mais tarde."` (pode indicar `retry`) |
| **Qualquer outro** | Transação **aprovada** |

### Exemplos práticos

Use qualquer cartão válido de teste da documentação Efí, ajustando o último dígito:

| Cenário | Número (exemplo) | CVV | Validade |
|---------|------------------|-----|----------|
| Aprovado | `4485785674290087` | `123` | `05/2029` |
| Dados inválidos | `448578567429008**1**` | `123` | `05/2029` |
| Recusa segurança | `448578567429008**2**` | `123` | `05/2029` |
| Recusa temporária | `448578567429008**3**` | `123` | `05/2029` |

**Bandeiras aceitas:** Visa, Mastercard, American Express (`amex`), Elo.

**Titular:** nome completo (mín. 2 palavras) e CPF/CNPJ válido do titular.

**Parcelas:** o sistema permite até **2x** (configurado no Ignis).

---

## 6. Testar o webhook isoladamente

Simule o POST que a Efí envia:

```bash
curl -X POST http://localhost:8000/webhooks/efi \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "notification=00000000-0000-0000-0000-000000000001"
```

Resposta esperada: `HTTP 200` com corpo `OK`.

Com o `queue:work` rodando, o job consulta a Efí com esse token e atualiza o pagamento correspondente (se existir `charge_id` vinculado).

---

## 7. Testes automatizados

```bash
# Docker
docker compose exec php ./vendor/bin/phpunit

# Local
./vendor/bin/phpunit
```

Cenários cobertos:

- Pagamento aprovado e recusado (`tests/Unit/Services/Payment/`)
- Idempotência (evitar cobrança duplicada)
- Webhook (`tests/Feature/Webhooks/EfiWebhookTest.php`)
- Livewire `processCardPayment` (`tests/Feature/Payment/CreditCardPaymentTest.php`)

---

## 8. Solução de problemas

| Sintoma | Possível causa | Ação |
|---------|----------------|------|
| *"Pagamento por cartão temporariamente indisponível"* | `EFI_PAYEE_CODE` vazio | Preencher no `.env` e `php artisan config:clear` |
| *"Sistema de pagamento não carregado"* | CDN da Efí bloqueada ou assets não compilados | Desativar bloqueador de anúncios; rodar `npm run build` |
| *"Não foi possível processar o pagamento"* | Credenciais/certificado inválidos | Conferir `EFI_CLIENT_ID`, `EFI_CLIENT_SECRET` e caminho do `.p12` |
| Erro de certificado | Arquivo ausente ou senha errada | Verificar `storage/app/private/efi/certificate.p12` e `EFI_CERTIFICATE_PASSWORD` |
| Pagamento fica em *Aguardando* para sempre | Webhook não chega ao servidor | Usar ngrok e atualizar `EFI_NOTIFICATION_URL` |
| E-mail de confirmação não chega | Fila parada | Rodar `php artisan queue:work`; ver Mailpit em `http://localhost:8025` |
| *"Um pagamento já está em processamento"* | Duplo clique / tentativa em menos de 5 min | Aguardar ou limpar registro `payments` com status `pending` no banco |

**Logs:** `storage/logs/laravel.log` (nunca deve conter número de cartão ou CVV).

**Debug Efí no front:** no console do navegador, a biblioteca aceita `EfiPay.CreditCard.debugger(true)` antes de tokenizar.

---

## 9. Checklist rápido antes do primeiro teste

- [ ] `EFI_SANDBOX=true`
- [ ] `EFI_CLIENT_ID` e `EFI_CLIENT_SECRET` de homologação
- [ ] Certificado em `storage/app/private/efi/certificate.p12`
- [ ] `EFI_PAYEE_CODE` preenchido
- [ ] `php artisan migrate` executado (tabela `payments` existe)
- [ ] `npm run build` ou `npm run dev` ativo
- [ ] `php artisan queue:work` rodando (webhooks e e-mails)
- [ ] Inscrição feita até a tela `/pagamento/{id}`
- [ ] Cartão de teste com final adequado ao cenário desejado

---

## 10. Referências no código

| Arquivo | Função |
|---------|--------|
| `app/Livewire/Pagamento.php` | Tela de pagamento e `processCardPayment()` |
| `app/Services/Payment/PaymentService.php` | Orquestra cobrança e confirmação |
| `app/Services/Efi/ChargeService.php` | `createOneStepCharge` na API Efí |
| `app/Http/Controllers/EfiWebhookController.php` | Recebe notificações |
| `resources/views/components/formulario-cartao-credito.blade.php` | Tokenização no browser |
| `config/efi.php` | Configuração centralizada |
| `routes/web.php` | Rota `POST /webhooks/efi` |
