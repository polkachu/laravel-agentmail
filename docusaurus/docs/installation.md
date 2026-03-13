---
sidebar_position: 2
title: Installation
---

# Installation

## Install via Composer

```bash
composer require polkachu/laravel-agentmail
```

## Service provider

The package uses Laravel's [package auto-discovery](https://laravel.com/docs/packages#package-discovery), so the service provider and `Agentmail` facade are registered automatically. No manual changes to `config/app.php` are needed.

## Publish the config file

```bash
php artisan vendor:publish --tag=agentmail
```

This copies `config/agentmail.php` into your application's `config/` directory. You only need to do this if you want to inspect or customise the config — the package works with `.env` variables alone.
