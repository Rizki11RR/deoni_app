# Shared hosting deployment

Target hosting must provide PHP 8.3, Composer, PostgreSQL, SSH or equivalent terminal access, and a document root that can point to Laravel `public/`. Do not expose project root, `.env`, `storage/`, or `vendor/` through the web server.

## First deployment

1. Create PostgreSQL database and user in hosting control panel.
2. Upload repository outside document root. Exclude `.env`, `node_modules`, and local database files.
3. Run from project root:

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. Set `.env` with production values, especially `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `DB_*`, and `FILESYSTEM_DISK`.
5. Point domain document root to `public/`.
6. Configure PHP-FPM or hosting scheduler to run queue and scheduler only when those features are enabled:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Updates

```bash
git pull --ff-only
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run update during maintenance window. Confirm login, product pages, stock pages, reports, and file uploads after deployment.

## Backup and restore verification

For Docker local environment, run from project root:

```bash
./scripts/backup-verify.sh
```

Script creates PostgreSQL custom-format backup, restores it into temporary `${POSTGRES_DB}_restore_check` database, then removes temporary database. Set `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_CONTAINER`, or `COMPOSE_COMMAND` when hosting names differ.

Shared hosting must provide its own PostgreSQL dump mechanism. Schedule `pg_dump` to storage outside web root, retain encrypted/off-host copies, and never commit backups or `.env`. Test restore into a separate database before trusting backup. Record date, backup path, restore result, and operator.

## Rollback

Do not restore production database blindly. Take fresh backup first, put application in maintenance mode, restore into a separate database for inspection, then switch only after checking schema and data. Code rollback must match database migration state.
