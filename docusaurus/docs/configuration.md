---
sidebar_position: 3
title: Configuration
---

# Configuration

## Environment variables

Add the following to your `.env` file:

```env
AGENTMAIL_API_KEY=your_api_key_here
AGENTMAIL_BASE_URL=https://api.agentmail.to
```

`AGENTMAIL_BASE_URL` defaults to `https://api.agentmail.to` and only needs to be set if you need to point at a different host (e.g. a staging environment).

## Config file

After running `php artisan vendor:publish --tag=agentmail`, the config file is available at `config/agentmail.php`:

```php
<?php

return [
    'api_key'  => env('AGENTMAIL_API_KEY'),
    'base_url' => env('AGENTMAIL_BASE_URL', 'https://api.agentmail.to'),
];
```

The service provider reads these values at boot time to construct an authenticated HTTP client, so you do not need to pass credentials anywhere else in your application.
