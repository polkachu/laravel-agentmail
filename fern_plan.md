# Plan: Fern Docs for laravel-agentmail

## Context

The `polkachu/laravel-agentmail` package wraps the AgentMail REST API for Laravel. We want to generate a beautiful documentation site using Fern Docs — the open-source Fern CLI + free Fern cloud hosting at `polkachu.docs.buildwithfern.com` with an optional custom domain.

---

## Directory Structure to Create

```
fern/
├── fern.config.json
├── docs.yml
├── openapi/
│   └── openapi.yaml           # Hand-written OpenAPI 3.0 spec for the AgentMail API
└── docs/
    └── pages/
        ├── introduction.mdx
        ├── installation.mdx
        ├── configuration.mdx
        └── usage/
            ├── inboxes.mdx
            └── error-handling.mdx
```

---

## Step-by-Step Implementation

### Step 1: Install Fern CLI

```bash
npm install -g fern-api
```

### Step 2: Initialize

```bash
cd /path/to/laravel-agentmail
fern init --docs
```

This scaffolds `fern/fern.config.json`, `fern/docs.yml`, and a starter page. Adjust the generated files per the steps below.

### Step 3: fern/fern.config.json

```json
{
  "organization": "polkachu",
  "version": "0.x.x"
}
```

Replace `version` with the output of `fern --version`.

### Step 4: fern/docs.yml

```yaml
instances:
  - url: polkachu.docs.buildwithfern.com

title: Laravel AgentMail

navigation:
  - section: Getting Started
    contents:
      - page: Introduction
        path: ./docs/pages/introduction.mdx
      - page: Installation
        path: ./docs/pages/installation.mdx
      - page: Configuration
        path: ./docs/pages/configuration.mdx
  - section: Usage
    contents:
      - page: Inboxes
        path: ./docs/pages/usage/inboxes.mdx
      - page: Error Handling
        path: ./docs/pages/usage/error-handling.mdx
  - api: API Reference

colors:
  accentPrimary:
    dark: '#7C3AED'
    light: '#7C3AED'
```

The `api: API Reference` entry tells Fern to auto-generate the interactive API reference from the OpenAPI spec in `fern/openapi/`.

### Step 5: fern/openapi/openapi.yaml

Hand-write an OpenAPI 3.0 spec covering the 5 endpoints the package wraps:

| Method | Path                     | Description                                                            |
| ------ | ------------------------ | ---------------------------------------------------------------------- |
| GET    | `/v0/inboxes`            | List inboxes (params: `limit`, `page_token`)                           |
| POST   | `/v0/inboxes`            | Create inbox (body: `username`, `domain`, `display_name`, `client_id`) |
| GET    | `/v0/inboxes/{inbox_id}` | Get a single inbox                                                     |
| PATCH  | `/v0/inboxes/{inbox_id}` | Update inbox `display_name`                                            |
| DELETE | `/v0/inboxes/{inbox_id}` | Delete inbox                                                           |

Include schemas for:

- `Inbox` — `pod_id`, `inbox_id`, `display_name`, `client_id`, `created_at`, `updated_at`
- `InboxCollection` — `inboxes[]`, `count`, `limit`, `next_page_token`
- `AgentmailError` — `name`, `message`

Security: `BearerAuth` via `Authorization: Bearer <api_key>` header.

### Step 6: MDX Content Pages

**introduction.mdx**

- What AgentMail is (programmatic email inboxes for AI agents)
- What this Laravel package does
- Quick feature list (typed DTOs, Facade, paginated listing, error handling)

**installation.mdx**

```bash
composer require polkachu/laravel-agentmail
php artisan vendor:publish --tag=agentmail
```

- Note: auto-discovered via Laravel package discovery (no manual registration)

**configuration.mdx**

- `.env` variables: `AGENTMAIL_API_KEY`, `AGENTMAIL_BASE_URL`
- Published config file at `config/agentmail.php`

**usage/inboxes.mdx**

- All 5 operations with PHP Facade examples (drawn from README)
- Pagination walkthrough with `nextPageToken`

**usage/error-handling.mdx**

- `AgentmailException` properties: `errorName`, `getMessage()`, `getCode()`
- Try/catch example

### Step 7: Validate and Publish

```bash
# Validate config
fern check

# Local preview (live reload at http://localhost:3000)
fern docs dev

# Publish to Fern cloud
fern generate --docs
```

First `generate` run will prompt for a free buildwithfern.com account. After auth, docs publish to `polkachu.docs.buildwithfern.com`.

### Step 8 (Optional): Custom Domain

Add to `docs.yml`:

```yaml
instances:
  - url: polkachu.docs.buildwithfern.com
    custom-domain: docs.yourdomain.com
```

Then set a DNS CNAME record pointing your domain at Fern's servers per their domain setup guide.

---

## Key Source Files to Reference

| File                                    | Used for                               |
| --------------------------------------- | -------------------------------------- |
| `src/Resources/Inboxes.php`             | Method signatures and endpoint details |
| `src/DTOs/Inbox.php`                    | OpenAPI `Inbox` schema field names     |
| `src/DTOs/InboxCollection.php`          | OpenAPI `InboxCollection` schema       |
| `src/Exceptions/AgentmailException.php` | Error handling docs                    |
| `config/agentmail.php`                  | Configuration page                     |
| `README.md`                             | Adapt existing examples into MDX       |
