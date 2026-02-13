---
name: alpine-integration
description: Alpine.js integration with Livewire
when-to-use: Client-side interactivity, entangle, $wire API
keywords: alpine, $wire, entangle, x-data, javascript
---

# Alpine.js Integration

## Decision Tree

```
Interactivity type?
├── Read property → $wire.property
├── Call method → $wire.method()
├── Two-way sync → $wire.entangle('prop')
├── Set value → $wire.set('prop', value)
├── Toggle boolean → $wire.toggle('prop')
└── Listen events → $wire.$on('event', fn)
```

## $wire API

| Property/Method | Purpose |
|-----------------|---------|
| `$wire.property` | Read property value |
| `$wire.method()` | Call component method |
| `$wire.set('prop', val)` | Set property |
| `$wire.toggle('prop')` | Toggle boolean |
| `$wire.$refresh()` | Refresh component |
| `$wire.$commit()` | Commit pending changes |

## $wire Properties

| Property | Returns |
|----------|---------|
| `$wire.$el` | Component DOM element |
| `$wire.$id` | Component ID |
| `$wire.$parent` | Parent $wire |

## @entangle

| Syntax | Sync Type |
|--------|-----------|
| `$wire.entangle('prop')` | Deferred |
| `$wire.entangle('prop').live` | Real-time |

## Event Methods

| Method | Purpose |
|--------|---------|
| `$wire.$on('event', fn)` | Listen to event |
| `$wire.$dispatch('event', {})` | Dispatch event |
| `$wire.$dispatchTo('comp', 'e')` | To component |
| `$wire.$dispatchSelf('event')` | To self |

## File Upload

| Method | Purpose |
|--------|---------|
| `$wire.$upload('prop', file, ...)` | Single file |
| `$wire.$uploadMultiple(...)` | Multiple files |
| `$wire.$removeUpload(...)` | Remove upload |

## @script Directive

| Usage | Purpose |
|-------|---------|
| `@script` | Define component JS |
| Access `$wire` | Inside @script |
| Lifecycle hooks | `$wire.$hook()` |

## Hooks

| Hook | When |
|------|------|
| `$wire.$hook('commit')` | Before commit |
| `$wire.$hook('message.sent')` | Request sent |
| `$wire.$hook('message.received')` | Response received |

## Watch Property

| Method | Purpose |
|--------|---------|
| `$wire.$watch('prop', fn)` | Watch changes |
| Callback args | (newVal, oldVal) |

## Best Practices

| DO | DON'T |
|----|-------|
| Use @entangle for sync | Manual event sync |
| $wire for simple reads | Complex Alpine state |
| @script for component JS | Inline large scripts |
| await $wire.method() | Ignore async |

→ **See template**: [AlpineIntegration.blade.md](templates/AlpineIntegration.blade.md)
