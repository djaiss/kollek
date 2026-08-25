<div align="center">

# KolleK

**The open source home for everything you collect.**

<p align="center">
 <picture>
  <source media="(prefers-color-scheme: dark)" srcset="docs/github/app_dark_mode.png">
  <img alt="The KolleK interface, showing a collection of catalogued items with their photos and details." src="docs/github/app_light_mode.png">
</picture>
</p>

KolleK is a self hostable web application for cataloguing collections of any kind, from comics and vinyl records to coins, watches, and wine. Organize your items, track every physical copy you own, record what you paid and what it is worth, and keep the whole history in one private, encrypted place.

[![Tests](https://github.com/djaiss/kollek/actions/workflows/tests.yml/badge.svg)](https://github.com/djaiss/kollek/actions/workflows/tests.yml)
[![Static analysis](https://github.com/djaiss/kollek/actions/workflows/static.yml/badge.svg)](https://github.com/djaiss/kollek/actions/workflows/static.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-2ea44f.svg)](LICENSE)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4.svg?logo=php&logoColor=white)](https://www.php.net)
[![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20.svg?logo=laravel&logoColor=white)](https://laravel.com)
[![PRs welcome](https://img.shields.io/badge/PRs-welcome-2ea44f.svg)](#contributing)

[What it is](#what-kollek-is) · [Features](#features) · [Run your own instance](#run-your-own-instance) · [Development](#development) · [API](#api) · [MCP server](#mcp-server) · [Contributing](#contributing)

</div>

---

## What KolleK is

Most collectors end up juggling spreadsheets, notes apps, and their own memory. KolleK replaces all of that with one focused tool, built to be run by anyone: a single collector on a small server, or a group sharing a catalogue with fine grained roles.

The model is small enough to hold in your head:

- An **account** is the workspace and the unit of tenancy. Everything below belongs to exactly one account.
- **Users** belong to an account and hold a role: owner, editor, or viewer.
- A **collection** holds **items**. Each item can carry a **type** (which decides its custom fields), sit in a **category**, and belong to a **set** or a **series**.
- An item has one or more **copies**, the physical things you actually own. A copy has a **condition**, a **location**, and a history of its own.
- **Tags**, **locations**, and **conditions** are shared across the whole account and reused everywhere.

## Features

### Cataloguing

- **[Collections](https://getkollek.com/en/docs/core-features/create-and-manage-collections).** Group your items into named collections, each with its own emoji, description, and currency.
- **[Types and custom fields](https://getkollek.com/en/docs/organizing/set-up-collection-types-and-custom-fields).** Describe what a collection holds with your own field definitions (text, number, date, boolean, or select), arranged in labelled groups so long forms stay readable. A dozen types ship ready made (Comics, Vinyl Records, Coins, Watches, Wine), and any type [exports and imports as JSON](https://getkollek.com/en/docs/organizing/import-and-export-a-collection-type).
- **[Items and photos](https://getkollek.com/en/docs/core-features/add-and-edit-items).** Add catalogue entries with a description, tags, and [multiple photos](https://getkollek.com/en/docs/core-features/add-photos-to-an-item) (one main visual, reorderable).
- **[Copies](https://getkollek.com/en/docs/core-features/track-the-copies-you-own).** Track each physical instance you own, with its own condition, storage location, acquisition date, price paid, and estimated value.
- **[Copy history](https://getkollek.com/en/docs/copy-history/copy-history).** Record transactions, valuations, insurance, maintenance, provenance, moves, and documents against a copy, then read them back as [one chronological timeline](https://getkollek.com/en/docs/copy-history/read-the-copy-timeline).

### Organization

- **[Categories, sets, and series](https://getkollek.com/en/docs/core-concepts/categories-sets-and-series).** [Nest items](https://getkollek.com/en/docs/organizing/organize-items-with-categories) inside a collection, [track a finite set](https://getkollek.com/en/docs/organizing/track-a-set-to-completion) towards a target count, and [tie a franchise together](https://getkollek.com/en/docs/organizing/group-a-franchise-with-series) across several collections.
- **[Locations](https://getkollek.com/en/docs/organizing/set-up-your-locations).** Record where items are physically stored, nested as deeply as you like (a shelf inside a box inside a room).
- **[Tags](https://getkollek.com/en/docs/organizing/manage-account-tags) and [conditions](https://getkollek.com/en/docs/organizing/manage-item-conditions).** Reusable labels that work across every collection, and item grades from New to Damaged that you can rename or extend.
- **[Statistics](https://getkollek.com/en/docs/insights/collection-statistics).** Roll up what a collection cost and what it is worth, in your account currency.

### Collaboration and safety

- **[Teams and roles](https://getkollek.com/en/docs/collaboration/invite-people).** Invite people into your account as [owners, editors, or viewers](https://getkollek.com/en/docs/collaboration/roles-in-practice), with permissions enforced on every write.
- **[Loans](https://getkollek.com/en/docs/core-features/loans-and-custody).** Lend a piece out or borrow one in, and see everything currently out of your hands in one place.
- **[Audit trail](https://getkollek.com/en/docs/core-features/activity-feed-and-audit-trail) and [trash](https://getkollek.com/en/docs/data-safety/restore-from-trash).** Every action is logged and surfaced as an activity feed, and deleted records stay recoverable.
- **[Account security](https://getkollek.com/en/docs/core-concepts/how-your-data-is-protected).** Sensitive values are encrypted at rest with Laravel's built in encryption, and sign in supports [two factor authentication](https://getkollek.com/en/docs/security/two-factor-authentication) and passwordless [magic links](https://getkollek.com/en/docs/security/magic-links).

### Platform

- **[JSON API](https://getkollek.com/en/docs/developers/api-overview).** A token authenticated REST API covers the whole catalogue. See [API](#api).
- **MCP server.** An assistant can run the instance administration over the Model Context Protocol, starting with the blog. See [MCP server](#mcp-server).
- **[Self hosting](https://getkollek.com/en/docs/self-hosting/install-with-docker).** A production Docker image and Compose stack, with data safe upgrades. See [Run your own instance](#run-your-own-instance).
- **[Localized](https://getkollek.com/en/docs/account-and-profile/change-your-language).** Available in English, French, Spanish, German, Portuguese (Brazil), Chinese (Simplified), and Japanese, each user picking their own. Translations live as one file per locale under `lang/`.
- **[Documentation](https://getkollek.com/en/docs).** A full portal, also served from your own instance at `/en/docs`.

A few capabilities are visible in the interface before they are finished, currently global search, collection sharing, and webhook delivery. The [feature status page](https://getkollek.com/en/docs/troubleshooting/feature-status) is the honest list of what works today.

## Run your own instance

You need Docker Engine 24 or newer with the Compose plugin.

```bash
git clone https://github.com/djaiss/kollek.git kollek
cd kollek

cp .env.docker.example .env

# Generate a unique application key and paste it into .env as APP_KEY.
docker compose run --rm app php artisan key:generate --show

# Review the passwords and APP_URL in .env, then start everything.
docker compose up -d --build
```

Open the URL you set in `APP_URL` (http://localhost:8000 by default) and create your account.

The stack runs the web server, a queue worker, a scheduler, and MySQL. Your database and uploaded files live in named volumes that are independent of the image, so pulling a newer version only ever applies new migrations and never resets your data. The full guide, including upgrades and backups, is in [docker/README.md](docker/README.md).

### Configuration

All configuration lives in the `.env` file. The values you are most likely to change:

| Variable          | Purpose                                                        |
| ----------------- | -------------------------------------------------------------- |
| `APP_NAME`        | The name shown across the interface.                           |
| `APP_URL`         | The public URL of your instance.                               |
| `APP_KEY`         | Encryption key. Unique per instance, never change it in place.  |
| `APP_LOCALE`      | Default interface language.                                    |
| `DB_*`            | Database connection settings.                                  |
| `MAIL_*`          | Outgoing email (SMTP or Resend). Defaults to the log.          |
| `FILESYSTEM_DISK` | Where uploaded item photos are stored.                         |

For a self hosted deployment, `.env.docker.example` documents the recommended production values.

## Development

For working on KolleK itself.

**Requirements**

- PHP 8.4 with the `gd`, `exif`, `bcmath`, `pcntl`, and `intl` extensions
- Composer 2
- Node 20 or newer, or Bun (the `composer setup` script uses Bun)
- A database (SQLite works with no setup; MySQL 8 is recommended for parity with production)

**Setup**

```bash
git clone https://github.com/djaiss/kollek.git kollek
cd kollek

composer setup
```

The `composer setup` script installs dependencies, creates your `.env`, generates an application key, runs the migrations, and builds the front end assets.

**Run the app**

```bash
composer dev
```

This starts the web server, the queue listener, the log viewer, and the Vite dev server together. KolleK is then available at http://localhost:8000.

### Tests and code style

```bash
# Run the full test suite.
composer test

# Format the code (Prettier, Laravel Pint, and Rector).
composer lint
```

The suite runs on [Pest](https://pestphp.com). Code style is Laravel Pint and Prettier, refactoring is Rector, static analysis is PHPStan, and all of them are enforced in continuous integration alongside the tests.

### Tech stack

| Layer      | Technology                                          |
| ---------- | --------------------------------------------------- |
| Backend    | PHP 8.4, Laravel 13                                 |
| Frontend   | Blade, Tailwind CSS 4, Alpine.js, Alpine Ajax, Vite |
| Database   | MySQL 8 (SQLite supported for local development)    |
| Auth       | Laravel Sanctum, Google Authenticator, magic links  |
| Queue      | Database driven jobs (Redis optional)               |
| Testing    | Pest, PHPStan, Laravel Pint, Rector                 |
| Deployment | Docker (nginx, PHP-FPM, supervisor)                 |

## API

KolleK ships a token authenticated JSON API that mirrors the web application. Authenticate with a personal access token (Laravel Sanctum) and send it as a bearer token:

```bash
curl https://your-instance.example/api/collections \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

The complete reference, with request and response examples for every endpoint, is generated from the codebase and served at `/en/docs/api` on your instance.

## MCP server

KolleK exposes its instance administration over the [Model Context Protocol](https://modelcontextprotocol.io), so an assistant can run the panel instead of a person clicking through it. Today it covers the blog on the marketing site: listing and reading entries, writing and filing them, publishing and archiving, and writing each language, marking it proofread or withdrawing it.

The server speaks streamable HTTP at `/mcp/instance` and is gated the same way the panel is. You need two things.

**1. A user who administers the instance.** Grant it from the command line (`docker compose exec app php artisan ...` on a Docker install):

```bash
php artisan kollek:make-instance-administrator you@example.com
```

**2. A personal access token for that user.** Create one in the interface, under Settings, then API keys (`/profile/api-keys/new`). It is the same kind of token the [JSON API](#api) uses.

Anyone else, including a signed in user who does not administer the instance, is answered 404, so the server does not announce itself.

**Connect a client.** With Claude Code:

```bash
claude mcp add --transport http kollek https://your-instance.example/mcp/instance \
  --header "Authorization: Bearer YOUR_TOKEN"
```

With any client that keeps its servers in a JSON config file:

```json
{
  "mcpServers": {
    "kollek": {
      "type": "http",
      "url": "https://your-instance.example/mcp/instance",
      "headers": {
        "Authorization": "Bearer YOUR_TOKEN"
      }
    }
  }
}
```

**The tools it offers**

| Tool                                | What it does                                                              |
| ----------------------------------- | ------------------------------------------------------------------------- |
| `list-blog-posts`                   | List the entries, filtered by status or searched by title and slug.        |
| `show-blog-post`                    | Read one entry and how far along every language of it is.                  |
| `create-blog-post`                  | Start an entry, as a draft, with its English text.                         |
| `update-blog-post`                  | Change the shelf, the tags, the featured flag, and the crawler settings.   |
| `publish-blog-post`                 | Put an entry in the public catalogue and purge the CDN cache.              |
| `archive-blog-post`                 | Retire an entry while its URL keeps answering.                             |
| `show-blog-post-translation`        | Read one language: its text, its URL, and its metadata.                    |
| `write-blog-post-translation`       | Write one language, creating it if it does not exist yet.                  |
| `copy-blog-post-source-translation` | Copy the English source across as a starting point to translate over.      |
| `publish-blog-post-translation`     | Mark a language proofread, so readers in it stop falling back to English.  |
| `withdraw-blog-post-translation`    | Take a language back off the public site.                                  |

There is deliberately no tool that deletes an entry. Archiving retires one without breaking the links pointing at it, and wiping an entry for good stays a decision somebody makes by hand in the panel.

## Contributing

Contributions are welcome, whether it is a bug report, a feature idea, a translation, or a pull request.

1. Fork the repository and create a branch for your change.
2. Follow the existing code style (Laravel Pint) and add tests for new behavior.
3. Make sure `composer test` and `composer lint` pass.
4. Open a pull request with a clear description of what changed and why.

New to the project? Issues labelled `good first issue` are a friendly place to start. What is planned, in progress, or deliberately unfinished lives in the [issues](https://github.com/djaiss/kollek/issues) and on the [feature status page](https://getkollek.com/en/docs/troubleshooting/feature-status).

## Security

If you discover a security vulnerability, please do not open a public issue. Instead, email the maintainer privately so it can be addressed before disclosure. Every report will be reviewed promptly.

## License

KolleK is open source software licensed under the [MIT license](LICENSE). It is built with [Laravel](https://laravel.com), [Tailwind CSS](https://tailwindcss.com), [Alpine.js](https://alpinejs.dev), and [Pest](https://pestphp.com), and inspired by every collector who has ever outgrown a spreadsheet.

<div align="center">

If KolleK is useful to you, consider giving it a star to help others find it.

</div>
