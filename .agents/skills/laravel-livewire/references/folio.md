---
name: folio
description: Laravel Folio file-based routing for Blade pages
file-type: markdown
---

# Folio - File-Based Routing

## When to Use

| Scenario | Use Folio? |
|----------|------------|
| Simple pages without controllers | Yes |
| Marketing/landing pages | Yes |
| Documentation pages | Yes |
| Complex business logic | No → Controller |
| API endpoints | No → API routes |
| Heavy data processing | No → Controller |

---

## Installation

```bash
composer require laravel/folio
php artisan folio:install
```

---

## Route Conventions

| File Path | URL | Notes |
|-----------|-----|-------|
| `pages/index.blade.php` | `/` | Root index |
| `pages/about.blade.php` | `/about` | Simple page |
| `pages/users/index.blade.php` | `/users` | Section index |
| `pages/users/[id].blade.php` | `/users/{id}` | Route parameter |
| `pages/users/[User].blade.php` | `/users/{user}` | Model binding |
| `pages/posts/[...ids].blade.php` | `/posts/{ids}` | Catch-all |

---

## Decision Tree

```
Need a page route?
├── Simple static page → pages/name.blade.php
├── Dynamic with ID → pages/[id].blade.php
├── Model binding → pages/[Model].blade.php
│   └── Custom key → pages/[Model:slug].blade.php
├── Catch-all segments → pages/[...segments].blade.php
└── Nested structure → pages/section/page.blade.php
```

---

## Key Functions

| Function | Purpose | Example |
|----------|---------|---------|
| `name()` | Named route | `name('users.show')` |
| `middleware()` | Apply middleware | `middleware(['auth'])` |
| `render()` | Custom response | `render(fn($view) => ...)` |
| `withTrashed()` | Include soft-deleted | `withTrashed()` |

---

## Quick Patterns

### Named Route
```php
<?php use function Laravel\Folio\name; name('dashboard'); ?>
```

### Middleware
```php
<?php use function Laravel\Folio\middleware; middleware(['auth', 'verified']); ?>
```

### Model Binding with Custom Key
```
pages/posts/[Post:slug].blade.php → /posts/my-post-slug
```

### Render Hook
```php
<?php
use function Laravel\Folio\render;
render(function ($view, Post $post) {
    abort_unless(auth()->user()->can('view', $post), 403);
    return $view->with('related', $post->related);
});
?>
```

---

## Multi-Path Configuration

```php
// AppServiceProvider.php
Folio::path(resource_path('views/pages/guest'))->uri('/');
Folio::path(resource_path('views/pages/admin'))
    ->uri('/admin')
    ->middleware(['*' => ['auth', 'verified']]);
```

---

## Subdomain Routing

```php
Folio::domain('{tenant}.example.com')
    ->path(resource_path('views/pages/tenant'));
```

---

## Commands

| Command | Purpose |
|---------|---------|
| `php artisan folio:page name` | Create page |
| `php artisan folio:list` | List all pages |
| `php artisan route:cache` | Cache routes |

---

## Best Practices

### DO
- Use for simple, view-centric pages
- Name routes for URL generation
- Apply middleware at path level for groups
- Use model binding for cleaner code

### DON'T
- Put complex logic in Blade files
- Forget `route:cache` in production
- Mix Folio and controller routes for same resource
