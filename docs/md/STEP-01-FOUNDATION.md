# STEP-01: Foundation

## Overview

**excalibur-l** is a full-stack application with a Laravel backend and React frontend. The project uses SQLite for the database, exposes a REST API, and serves a React SPA that connects to the API via a Vite dev proxy.

## Environment

- **Laravel**: 12.x (PHP ^8.2)
- **React**: 19.x with Vite 7.x (in `/frontend`)
- **Database**: SQLite (`database/database.sqlite`)

## Backend Setup

### Laravel Configuration

- Created via `composer create-project laravel/laravel excalibur-l`
- API routes in `routes/api.php` (mounted under `/api`)
- CORS configured in `config/cors.php` for `api/*` paths (all origins allowed)
- Bootstrap registers API routes in `bootstrap/app.php`

### Database

- **Driver**: SQLite
- **Path**: `database/database.sqlite`
- **`.env`**: `DB_CONNECTION=sqlite` (no host/database vars needed)
- **Migrations**: Users, cache, and jobs tables applied

### Test API Route

- **Endpoint**: `GET /api/test`
- **Response**: `{ "status": "ok" }`

## Frontend Setup

### React + Vite

- Scaffolded with `npm create vite@latest frontend -- --template react`
- Location: `/frontend`
- React 19, Vite 7, `@vitejs/plugin-react`

### API Connectivity

- Vite proxy forwards `/api` requests to Laravel (`http://localhost:8000`)
- React fetches `/api/test` on load and displays the status
- Avoids CORS issues in development by using the proxy

## API Connectivity

```
Browser (localhost:5173) → fetch('/api/test') → Vite proxy → Laravel (localhost:8000/api/test)
```

- React requests go to the same origin (`/api/test`), so the browser sends them to the Vite dev server.
- Vite proxies matching `/api` requests to `http://localhost:8000`.
- Laravel handles the request and returns JSON; CORS is not needed when using the proxy.

## Files & Structure Created

```
excalibur-l/
├── bootstrap/app.php          # API routes registered
├── config/cors.php            # CORS configuration (published)
├── database/
│   └── database.sqlite        # SQLite database file
├── frontend/
│   ├── src/
│   │   ├── App.jsx            # Fetches /api/test, displays status
│   │   └── main.jsx
│   ├── vite.config.js         # Proxy /api → localhost:8000
│   └── package.json
├── routes/
│   └── api.php                # GET /api/test
└── .env                       # DB_CONNECTION=sqlite
```

## How to Run Locally

**Project path:** `C:\Dox\Projects\excalibur-l`

**Terminal 1 – Laravel backend:**
```powershell
cd C:\Dox\Projects\excalibur-l
php artisan serve
```

**Terminal 2 – React frontend:**
```powershell
cd C:\Dox\Projects\excalibur-l\frontend
npm run dev
```

## Verification

1. **Laravel API**: Open `http://localhost:8000/api/test` → expect `{"status":"ok"}`.
2. **Vite proxy**: Open `http://localhost:5173/api/test` → expect same JSON.
3. **React app**: Open `http://localhost:5173` → expect “Laravel API: ok” in the UI.
4. **Database**: Run `php artisan migrate:status` → all migrations should show as Ran.
