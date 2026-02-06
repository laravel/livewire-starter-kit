---
name: reverb
description: Laravel Reverb WebSocket server for real-time broadcasting
file-type: markdown
---

# Reverb - WebSocket Server

## When to Use

| Scenario | Use Reverb? |
|----------|-------------|
| Real-time notifications | Yes |
| Live chat/messaging | Yes |
| Presence channels | Yes |
| Simple HTTP requests | No |

---

## Installation

```bash
php artisan install:broadcasting
```

---

## Environment Variables

| Variable | Purpose | Example |
|----------|---------|---------|
| `REVERB_APP_ID` | App ID | `my-app-id` |
| `REVERB_APP_KEY` | Public key | `my-app-key` |
| `REVERB_APP_SECRET` | Secret key | `my-app-secret` |
| `REVERB_HOST` | Public hostname | `ws.example.com` |
| `REVERB_PORT` | Public port | `443` |
| `REVERB_SERVER_HOST` | Bind address | `0.0.0.0` |
| `REVERB_SERVER_PORT` | Server port | `8080` |
| `REVERB_SCALING_ENABLED` | Redis scaling | `true` |

---

## Commands

| Command | Purpose |
|---------|---------|
| `reverb:start` | Start server |
| `reverb:start --debug` | Debug mode |
| `reverb:restart` | Graceful restart |

---

## Production Setup

### System Limits
```bash
# /etc/security/limits.conf
forge soft nofile 10000
forge hard nofile 10000
```

### Event Loop (>1000 connections)
```bash
pecl install uv
```

### Nginx Proxy
```nginx
location / {
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_pass http://0.0.0.0:8080;
}
```

### Supervisor
```ini
[program:reverb]
command=php artisan reverb:start
autostart=true
autorestart=true

[supervisord]
minfds=10000
```

---

## SSL with Herd/Valet

```bash
php artisan reverb:start --hostname="laravel.test"
```

---

## Pulse Monitoring

```php
'recorders' => [
    ReverbConnections::class => ['sample_rate' => 1],
    ReverbMessages::class => ['sample_rate' => 1],
],
```

---

## Horizontal Scaling

1. `REVERB_SCALING_ENABLED=true`
2. Configure central Redis
3. Deploy multiple instances
4. Load balancer in front

---

## Best Practices

### DO
- Use Supervisor in production
- Enable ext-uv for high connections
- Monitor with Pulse

### DON'T
- Run without process manager
- Skip SSL in production
- Ignore file limits
