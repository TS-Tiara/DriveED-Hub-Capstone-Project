# Hostinger Deployment Bundle (Laravel)

This folder contains a production-ready template for deploying this Laravel app to Hostinger shared hosting.

## Recommended Layout on Hostinger

- Laravel app root (private): `/home/USERNAME/domains/YOUR_DOMAIN/laravel_app`
- Public web root: `/home/USERNAME/domains/YOUR_DOMAIN/public_html`

Keep the full Laravel project outside `public_html`.
Only place the files from `Hostinger/public_html/` inside `public_html`.

## Files in This Bundle

- `public_html/index.php` : Front controller pointing to `../laravel_app`
- `public_html/.htaccess` : Rewrite/security rules for Apache
- `.env.hostinger.example` : Production environment template
- `cron/laravel-scheduler.txt` : Cron line for scheduler
- `cron/queue-worker.txt` : Cron line for queue processing
- `post-deploy-commands.txt` : Commands to run after every upload/update

## First Deployment Steps

1. Upload app files to `laravel_app` (exclude `.git`, `node_modules`, and local cache files).
2. Upload `Hostinger/public_html/index.php` and `Hostinger/public_html/.htaccess` into Hostinger `public_html`.
3. Create `.env` inside `laravel_app` using `.env.hostinger.example`.
4. Run the commands listed in `post-deploy-commands.txt`.
5. Add cron jobs from `cron/*.txt` in hPanel -> Cron Jobs.
6. Verify: login, reports page, payments flow, file uploads, and mail sending.

## Update Flow (Future Deployments)

1. Backup database and `.env`.
2. Upload changed source files to `laravel_app`.
3. Run post-deploy commands.
4. Smoke test critical pages and background features.

## Important Notes

- `APP_ENV` must be `production` and `APP_DEBUG=false`.
- Ensure `storage` and `bootstrap/cache` are writable.
- Keep `APP_KEY` unchanged after first production launch.
- If you use queues, keep the queue cron active.
