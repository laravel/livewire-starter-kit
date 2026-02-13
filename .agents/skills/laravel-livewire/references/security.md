---
name: security
description: Livewire security best practices
when-to-use: Authorization, rate limiting, protecting data
keywords: authorize, locked, sensitive, rate limit, CSRF
---

# Security

## Decision Tree

```
Security concern?
├── Protect property → #[Locked]
├── Hide from snapshot → #[Sensitive]
├── Check permission → $this->authorize()
├── Limit requests → rateLimit()
├── Protect method → Use protected/private
└── CSRF → Automatic in forms
```

## #[Locked] Attribute

| Purpose | Effect |
|---------|--------|
| Prevent client modification | Throws exception |
| Use for | IDs, flags, sensitive data |
| Cannot be changed | Via wire:model or JS |

## #[Sensitive] Attribute

| Purpose | Effect |
|---------|--------|
| Hide from snapshots | Not in HTML |
| Use for | API keys, tokens |
| Still usable | In component |

## Authorization

| Method | Usage |
|--------|-------|
| `$this->authorize('action', $model)` | Policy check |
| Throws exception | If unauthorized |
| `Gate::allows()` | Manual check |
| `auth()->user()->can()` | User check |

## Protected Methods

| Visibility | Callable from Client |
|------------|---------------------|
| `public` | ✅ Yes |
| `protected` | ❌ No |
| `private` | ❌ No |

## Rate Limiting

| Method | Purpose |
|--------|---------|
| `$this->rateLimit(10)` | 10 per minute |
| `$this->rateLimit(5, key: 'user-'.id)` | Per user |
| `TooManyRequestsException` | Caught |
| `$e->secondsUntilAvailable` | Retry time |

## CSRF Protection

| Feature | Status |
|---------|--------|
| Forms | Automatic |
| AJAX requests | Automatic |
| Token refresh | Automatic |
| No @csrf needed | In wire:submit |

## XSS Protection

| Syntax | Behavior |
|--------|----------|
| `{{ $var }}` | Escaped (safe) |
| `{!! $var !!}` | Raw HTML (careful) |

## Route Middleware

| Pattern | Usage |
|---------|-------|
| `->middleware('auth')` | Require auth |
| `->middleware('can:action,model')` | Policy |
| `->middleware('verified')` | Email verified |

## Best Practices

| DO | DON'T |
|----|-------|
| #[Locked] for IDs | Expose sensitive IDs |
| authorize() in actions | Skip authorization |
| protected for internal | Public everything |
| Rate limit auth actions | Allow unlimited |

→ **See template**: [SecureComponent.php.md](templates/SecureComponent.php.md)
