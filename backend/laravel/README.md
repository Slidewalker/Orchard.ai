# Orchard.ai Laravel MCP - Docker Compose

Quick Docker Compose setup for the Orchard.ai Laravel backend (MCP).

Files added:

- `docker-compose.yml` — app, nginx, mysql, redis
- `Dockerfile` — PHP-FPM image with common extensions
- `php-entrypoint.sh` — installs composer deps and generates `APP_KEY` if missing
- `nginx/default.conf` — nginx configuration for Laravel `public/`
- `.env.example` — example environment configured to talk to the compose services

Usage

1. Clone the Orchard.ai repo and change into the Laravel backend folder:

```bash
git clone https://github.com/Slidewalker/Orchard.ai.git
cd Orchard.ai/backend/laravel
```

2. Copy the scaffolded files into that directory (or move this folder's contents into `backend/laravel`).

3. Start the backend service:

```bash
docker compose up -d --build
```

4. Open http://localhost:8081

This backend is a minimal PHP Alpha-01 handler and does not require Composer or Laravel migrations.

Notes

- If the repo already contains its own Docker files, use this as a reference or merge carefully.
- Adjust `MYSQL_*` values in `docker-compose.yml` and `.env.example` as needed.
- To run artisan/Composer interactively:

```bash
docker compose exec app bash
composer install
php artisan migrate
```
