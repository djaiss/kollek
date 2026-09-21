# Self-hosting KolleK with Docker

KolleK ships a production Docker image and a Compose stack so you can run your
own instance. Self-hosting is a first-class, supported use case.

## What the stack runs

| Service     | Role                                              |
| ----------- | ------------------------------------------------- |
| `app`       | nginx + PHP-FPM web server. Runs migrations on boot. |
| `queue`     | Queue worker (processes the `high`, `default`, `low` queues). |
| `scheduler` | Runs the artisan schedule (e.g. the daily inactive-user cleanup). |
| `mysql`     | MySQL 8.4 database.                               |

The `app`, `queue` and `scheduler` services all run the same image; only the
role differs. Sessions, cache and the queue are database-backed by default, so
no Redis or extra service is required.

## Requirements

- Docker Engine 24+ and the Compose plugin (`docker compose`).

## Quick start

```bash
cp .env.docker.example .env

# Generate a unique application key and paste it into .env as APP_KEY.
docker compose run --rm app php artisan key:generate --show

# Review the database passwords and APP_URL in .env, then start everything.
docker compose up -d --build
```

KolleK is then available at the `APP_URL` you set (http://localhost:8000 by
default). Create your account from the registration page.

## Configuration

All configuration lives in `.env` (copied from `.env.docker.example`). The
values that matter most:

- `APP_KEY`: required, unique, and shared by every container. Never change it
  on a running instance, or existing sessions and encrypted data become
  unreadable.
- `APP_URL`: the public URL of your instance.
- `APP_PORT`: the host port the web container is published on.
- `TRUSTED_PROXIES`: required when a reverse proxy terminates TLS. See below.
- `DB_PASSWORD` and `DB_ROOT_PASSWORD`: set real secrets before first boot.
- `MAIL_*`: configure SMTP or Resend to send real email (defaults to the log).
- `HOSTED_INSTANCE`: leave it `false`. It marks the managed instance we run and
  charge for, where an account holds ten items for free before it has to be
  unlocked. A self hosted instance has no item limit and nothing to buy.

## Running behind a reverse proxy

The `app` container speaks plain http on port 80 and never terminates TLS
itself, so a real instance almost always has something in front of it: Traefik,
Caddy, nginx, or a CDN such as Cloudflare.

That proxy has to tell the application what it hid, and the application has to
be told to believe it. Set `TRUSTED_PROXIES` in `.env`:

```dotenv
TRUSTED_PROXIES=*
```

Use `*` when the proxy has no fixed address, which is the normal case for a
container on the same Docker network. Name the addresses or CIDR ranges instead
(`TRUSTED_PROXIES=10.0.0.0/8,172.18.0.0/16`) if you know them and want to be
strict.

Leave it empty and the `X-Forwarded-*` headers are ignored, which shows up as:

- **Mixed content.** Stylesheets and scripts are written as `http://` on an
  `https://` page, and the browser blocks them, so the instance loads unstyled
  or not at all.
- **One shared rate limit.** Every visitor appears to come from the proxy, so
  the six attempts a minute on the sign in form are shared by everyone, and the
  "new sign in" security emails report the proxy's address.

Two things to get right alongside it:

- `APP_URL` must be the public `https://` URL. Email links and the trusted host
  check are both built from it, so an instance reachable at
  `https://kollek.example.com` needs exactly that, not `http://localhost:8000`.
- Stop publishing the container to the host. With `TRUSTED_PROXIES=*` the
  application believes the forwarded headers of whoever connects to it, so the
  proxy should be the only thing that can. Remove the `ports:` block from the
  `app` service in `docker-compose.yml` and put the proxy on the same network,
  or bind it to the loopback address only (`APP_PORT` published as
  `127.0.0.1:8000:80`).

A Traefik label set for the `app` service looks like this, with no `ports:`
block and Traefik attached to the same network:

```yaml
labels:
  - "traefik.enable=true"
  - "traefik.http.routers.kollek.rule=Host(`kollek.example.com`)"
  - "traefik.http.routers.kollek.entrypoints=websecure"
  - "traefik.http.routers.kollek.tls.certresolver=letsencrypt"
  - "traefik.http.services.kollek.loadbalancer.server.port=80"
```

## Data and persistence

Two named volumes hold all state, independent of the image:

- `db-data`: the MySQL database.
- `storage-data`: uploaded item photos and application logs
  (`/var/www/html/storage`).

Because the data lives in volumes, replacing or upgrading the image never
touches it.

## Upgrading

Upgrades are designed to be safe for an existing database:

```bash
git pull                       # or pull a new tagged image
docker compose up -d --build
```

On boot the `app` container runs `php artisan migrate --force`, which applies
**only new, pending migrations**. It never runs `migrate:fresh` or
`migrate:refresh`, so your data is preserved. To manage migrations yourself,
set `RUN_MIGRATIONS=false` and run `docker compose exec app php artisan migrate
--force` when you choose.

Every container also runs `php artisan docs:cache` on boot, which builds the
documentation portal index (served at `/{locale}/docs`, e.g. `/en/docs`) from
the Markdown files so pages render without scanning the disk on each request.

### One-off step after upgrading to the photos screen

The photos screen searches encrypted file names through an index the app keeps
beside them, and shows the pixel size of each image. Neither exists for photos
uploaded before that release, so the screen finds nothing until the index is
built once:

```bash
docker compose exec app php artisan photos:rebuild-search-index
```

It reads every photo off the disk, so give it a moment on a large library. It is
safe to run again at any time, and photos uploaded from then on are indexed as
they arrive.

Account wide search reads an index of its own, covering items, collections,
copies, photos, loans, locations, sets, series, categories, tags and documents.
The migration creates its table, but only this command fills it, so search finds
nothing on an existing instance until it has run once:

```bash
docker compose exec app php artisan search:rebuild-index
```

It walks every record in every account, so give it a moment on a large instance.
From then on the index keeps itself up to date as records change. Add
`--type=item` to rebuild one kind of record only.

## Administering the instance

The instance administration lives at `/instance-admin` and lists every account
and user on the instance. It is gated on a per user flag that nobody has by
default, so grant it to yourself once after registering your user:

```bash
docker compose exec app php artisan kollek:make-instance-administrator you@example.com
```

Pass `--revoke` to take it away again. The flag is separate from the owner,
editor and viewer roles, which only ever apply inside a single account.

## Backups

```bash
# Database
docker compose exec mysql \
  mysqldump -u root -p"$DB_ROOT_PASSWORD" "$DB_DATABASE" > kollek-backup.sql

# Uploaded files
docker run --rm -v kollek_storage-data:/data -v "$PWD":/backup alpine \
  tar czf /backup/kollek-storage.tar.gz -C /data .
```

## Common commands

```bash
docker compose logs -f app                 # tail the web logs
docker compose exec app php artisan tinker # a REPL inside the app
docker compose ps                          # service status and health
docker compose down                        # stop (keeps the volumes)
docker compose down -v                     # stop and DELETE all data
```

## Troubleshooting

- **`APP_KEY is not set`.** Run the `key:generate --show` step above and paste
  the value into `.env`.
- **Web container is unhealthy.** Check `docker compose logs app`; it usually
  means the database was not reachable or a migration failed.
- **Uploads fail.** Ensure the `storage-data` volume is mounted and writable
  (the entrypoint fixes ownership automatically on boot).
- **The page loads unstyled over https, or the browser reports blocked mixed
  content.** A reverse proxy is terminating TLS and `TRUSTED_PROXIES` is not
  set. See "Running behind a reverse proxy".
- **Every request answers 400 after setting `TRUSTED_PROXIES`.** The host being
  asked for does not match `APP_URL`, which is what the trusted host check
  compares against. Set `APP_URL` to the public URL, including `https://`.
