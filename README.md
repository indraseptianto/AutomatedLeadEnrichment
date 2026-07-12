# Automated Lead Enrichment

**MVP workflow for automated inbound lead enrichment and onboarding.** Combines **n8n** for workflow automation with **Laravel + Filament** for lead management.

---

## 📋 Summary

| Component | Technology |
|----------|-----------|
| **Workflow Engine** | n8n — webhook intake, enrichment, routing |
| **Backend API** | Laravel 11 — REST API for lead CRUD |
| **Admin Panel** | Filament 3 — lead management panel |
| **Database** | SQLite (default), switchable to MySQL/PostgreSQL |

---

## 🏗️ Architecture

```text
[Inbound Lead] → n8n Webhook
                    ↓
           Validate Required Fields
                    ↓
             Normalize Lead Data
                    ↓
          Mock Company Enrichment
          (company size, industry, etc.)
                    ↓
         ┌──────────────────────┐
         │  POST /api/leads      │ ← Laravel API
         │  (Laravel + Filament)  │
         └──────────────────────┘
                    ↓
         Send Welcome Email
         Send Sales Notification
                    ↓
           Respond Success → 201
```

### Error Handling

```text
Missing required fields  → 400 Bad Request
Laravel API down         → internal alert → 502 Bad Gateway
Enrichment failed        → lead is still created with partial data
```

---

## 🚀 Local Setup

### Prerequisites

- PHP 8.2+
- Composer 2.x
- Node.js 18+ (optional, for Filament assets)
- n8n (self-hosted or cloud)

### 1. Clone & Install Laravel

```bash
git clone https://github.com/indraseptianto/AutomatedLeadEnrichment.git
cd AutomatedLeadEnrichment
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Database

Default SQLite:

```bash
touch database/database.sqlite
php artisan migrate
```

Switch to MySQL/PostgreSQL by editing `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leads
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Create Admin User

```bash
php artisan make:filament-user
# Fill in: name, email, password
```

### 4. Run Dev Server

```bash
php artisan serve
```

- **Filament Panel:** `http://localhost:8000/admin`
- **API:** `http://localhost:8000/api/leads`

### 5. Setup n8n Workflow

1. Open n8n → **Import from File**
2. Select `n8n/lead-enrichment-workflow.json`
3. Update credentials:
   - **HTTP Request** node → URL: `http://your-server:8000/api/leads`
   - **Header** → `Authorization: Bearer YOUR_API_TOKEN` (or a custom token)
4. **Activate** workflow

---

## 📡 API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/api/health` | Health check | No |
| `GET` | `/api/leads` | List all leads (paginated) | No* |
| `POST` | `/api/leads` | Create new lead | No* |
| `GET` | `/api/leads/{id}` | Get single lead | No* |
| `PUT/PATCH` | `/api/leads/{id}` | Update lead | No* |
| `DELETE` | `/api/leads/{id}` | Delete lead | No* |

*\*For production, add auth middleware (Sanctum token or API key).*

### Example: POST /api/leads

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+6281234567890",
  "company": "Tech Corp",
  "website": "https://techcorp.com",
  "linkedin_url": "https://linkedin.com/company/techcorp",
  "source": "n8n",
  "industry": "Technology",
  "location": "Jakarta, Indonesia",
  "notes": "Inbound lead from website form"
}
```

**Response (201):**

```json
{
  "ok": true,
  "message": "Lead created successfully",
  "lead": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    ...
    "status": "new"
  }
}
```

---

## ⚙️ n8n Workflow Detail

File: `n8n/lead-enrichment-workflow.json`

### Nodes

```text
1. Webhook Lead Intake       — POST /webhook/lead-enrich
2. Validate Required Fields  — IF node: name + email required
3. Normalize Lead            — Code node: format data
4. Mock Company Enrichment   — Code node: generate mock enrichment data
                              (company_size, industry, linkedin_url)
5. POST Lead to Laravel API  — HTTP Request → /api/leads
6. Send Welcome Email        — Email node (mock/SMTP)
7. Send Sales Notification   — Email node (internal)
8. Respond Success           — Respond to Webhook
```

### Environment Variables (n8n)

| Variable | Default | Description |
|----------|---------|-------------|
| `LARAVEL_API_URL` | `http://localhost:8000/api/leads` | Laravel API endpoint |
| `AUTOMATION_TOKEN` | `demo-secret-token` | Token for inter-service auth |

---

## 🧪 Testing

### Test API via curl

```bash
# Health check
curl http://localhost:8000/api/health

# Create lead
curl -X POST http://localhost:8000/api/leads \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","source":"manual"}'

# List leads
curl http://localhost:8000/api/leads
```

### Test n8n → Laravel flow

```bash
# Replace with your n8n URL
curl -X POST https://your-n8n.com/webhook/lead-enrich \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@company.com",
    "phone": "+6289876543210",
    "company": "Startup Inc"
  }'
```

### Run PHPUnit Tests

```bash
php artisan test
```

---

## 🧠 AI Utilization

This project was developed with the assistance of **Hermes Agent** (Nous Research):

- **Workflow planning** — AI structured the n8n flow based on the lead enrichment brief
- **Code generation** — Controller, model, migration, and Filament resource were generated by AI
- **Payload examples** — Example request/response payloads composed by AI
- **Documentation** — This README was written with AI assistance
- **Debugging** — AI helped diagnose errors and optimize structure

---

## 📁 Folder Structure

```text
AutomatedLeadEnrichment/
├── n8n/
│   └── lead-enrichment-workflow.json    # n8n workflow export
├── app/
│   ├── Filament/
│   │   └── Resources/
│   │       └── LeadResource.php         # Filament panel config
│   ├── Http/
│   │   └── Controllers/
│   │       └── LeadController.php       # API controller
│   └── Models/
│       └── Lead.php                     # Eloquent model
├── database/
│   └── migrations/
│       └── ..._create_leads_table.php   # Migration
├── routes/
│   └── api.php                          # API route definitions
└── README.md
```

---

## 🔄 Production Deployment

### VPS / Server

```bash
# 1. Clone & install
git clone https://github.com/indraseptianto/AutomatedLeadEnrichment.git
cd AutomatedLeadEnrichment
composer install --optimize-autoloader --no-dev

# 2. Environment
cp .env.example .env
# Edit .env: APP_URL, DB credentials, etc.
php artisan key:generate

# 3. Migrate & optimize
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Serve via nginx
# Point root to project/public/
# PHP-FPM + nginx reverse proxy
```

### Nginx Config (Optional)

```nginx
server {
    listen 80;
    server_name leads.yourdomain.com;
    root /var/www/AutomatedLeadEnrichment/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 📝 Assumptions & Notes

1. **Enrichment data is mocked** for MVP. In production, replace the Mock Enrichment node with a real API (Apollo, Clearbit, People Data Labs, or an approved LinkedIn data provider).
2. **Welcome email & sales notification** use n8n email nodes. In production, integrate with SendGrid / Mailgun / SMTP.
3. **API endpoint auth** is intentionally open for MVP. For production, enable Sanctum middleware or n8n automation token.
4. **SQLite** is chosen for easy setup. For production scale, use MySQL/PostgreSQL.
5. **Filament admin panel** can be further customized: add dashboard widgets, CSV export, etc.

---

## 📄 License

MIT — Free for personal & commercial use.

---

*Built by [indraseptianto](https://github.com/indraseptianto) — Automation & AI Solutions*
