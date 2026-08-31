# nuxgame-test

Laravel application with server-rendered Blade views, run locally via Laravel Sail (Docker).
There is no frontend build step — no Node, no npm, no Vite.

## How it works

1. `/` shows the registration form (Username + Phone number).
2. Registering issues a unique link and redirects straight to it.
3. That link — `/luck/{token}` — is **page A**. The token is the only credential:
   no login, no session. Anyone holding the link can open it for **7 days**.
4. Page A offers: regenerate the link, deactivate the link, `Imfeelinglucky`,
   and `History` (the last 3 results).
5. An unknown, deactivated, regenerated or expired token returns **404**. The
   7-day window is enforced on every request, so no scheduler is required for
   links to expire. `app:deactivate-expired-links` (scheduled daily) only tidies
   up the stored status.

## Prerequisites

- Docker Desktop (or compatible) running — **this is the only requirement.**
- PHP and Composer are **not** needed on the host. The app targets PHP 8.4, and
  every PHP step here — including the initial `composer install` that creates
  `vendor/` before Sail exists — runs inside a container. See step 3.
- Node.js and npm are **not** required, on the host or anywhere else.

## 1. Clone and configure environment

```bash
git clone <repo-url> nuxgame-test
cd nuxgame-test
cp .env.example .env
```

No edits are needed — `.env.example` already points at the Sail MySQL service
and the `nuxgame-test.loc` domain set up in the next step.

## 2. Add a hosts entry

Sail serves the app on port 80 by default. Point the local domain at your machine:

```bash
echo "127.0.0.1 nuxgame-test.loc" | sudo tee -a /etc/hosts
```

## 3. Install PHP dependencies

`compose.yaml` builds from `vendor/laravel/sail/`, so `vendor/` has to exist
before Sail can start. Install it with Composer running in a throwaway
container — no host PHP involved:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd)":/opt \
    -w /opt \
    laravelsail/php84-composer:latest \
    composer install --no-interaction
```

The `-u` flag makes the resulting `vendor/` belong to you rather than to root.

> If you happen to have a host PHP satisfying `composer.json`'s `^8.3` constraint,
> a plain `composer install` does the same job.

## 4. Start Sail

```bash
./vendor/bin/sail up -d
```

This starts two containers:
- `nuxgame-test-laravel.test-1` — the app
- `nuxgame-test-mysql-1` — MySQL

If MySQL fails to start with a message like `port is already allocated`, you
already have something on host port 3306. Pick a free port in `.env` and run
`./vendor/bin/sail up -d` again:

```
FORWARD_DB_PORT=3307
```

This only changes how you reach MySQL *from the host* (e.g. a GUI client). The
app talks to the container over the Docker network as `mysql:3306`, so
`DB_PORT` stays at `3306`.

## 5. Generate the app key and run migrations

Both run inside the app container:

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

The site is now available at **http://nuxgame-test.loc**.

## Optional: cleaning up expired links

Expiry is enforced when a link is used, so this is not needed for correctness.
To also flip stored statuses to `inactive` once a link passes 7 days:

```bash
./vendor/bin/sail artisan app:deactivate-expired-links  # one-off
./vendor/bin/sail artisan schedule:work                 # or run it daily
```

## Running tests

The suite runs on PHPUnit. Always run tests through Sail (the `.env` `DB_HOST=mysql` only resolves inside the Docker network):

```bash
./vendor/bin/sail artisan test
# or target a specific test class/method
./vendor/bin/sail artisan test --compact --filter=SomeTest
```

## Useful commands

```bash
./vendor/bin/sail up -d        # start containers
./vendor/bin/sail down         # stop containers
./vendor/bin/sail artisan ...  # run Artisan commands
./vendor/bin/sail composer ... # run Composer commands
./vendor/bin/sail pint --dirty # fix PHP code style
```
