---
name: events
description: Livewire event dispatching and listening
when-to-use: Component communication, browser events, Alpine integration
keywords: dispatch, listener, on, browser events, alpine
---

# Events

## Decision Tree

```
Communication type?
├── Component → Component → dispatch() + #[On]
├── Parent → Child → Props or $parent
├── Child → Parent → dispatch() to parent
├── Component → Browser → dispatch() + JS listener
├── Browser → Component → JS dispatch + wire:event
└── Alpine ↔ Livewire → $wire, @entangle
```

## Dispatch Methods

| Method | Target |
|--------|--------|
| `$this->dispatch('event', data)` | Global |
| `$this->dispatchTo('component', 'event')` | Specific component |
| `$this->dispatchSelf('event')` | Self only |

## Listening Methods

| Method | Usage |
|--------|-------|
| `#[On('event')]` | Attribute on method |
| `#[On(['evt1', 'evt2'])]` | Multiple events |
| `wire:event="method"` | In Blade |
| `wire:event.window="method"` | Global in Blade |

## JavaScript API

| Method | Usage |
|--------|-------|
| `Livewire.dispatch('event', {})` | Global dispatch |
| `Livewire.dispatchTo('comp', 'event')` | To component |
| `Livewire.on('event', callback)` | Listen globally |

## Browser Events

| Source | Target |
|--------|--------|
| PHP `dispatch()` | `x-on:event.window` |
| Alpine `$dispatch()` | `wire:event` |
| JS `dispatchEvent()` | `wire:event.window` |

## $wire in Alpine

| Method | Purpose |
|--------|---------|
| `$wire.property` | Access property |
| `$wire.method()` | Call method |
| `$wire.set('prop', val)` | Set property |
| `$wire.$refresh()` | Refresh component |
| `$wire.$dispatch('event')` | Dispatch event |

## @entangle

| Syntax | Behavior |
|--------|----------|
| `$wire.entangle('prop')` | Two-way sync |
| `$wire.entangle('prop').live` | Real-time sync |

## Event Data

| Pattern | Access |
|---------|--------|
| `dispatch('e', id: 1)` | Named params |
| `#[On('e')] fn($id)` | Receive params |
| `$event.detail.id` | In JavaScript |

## Best Practices

| DO | DON'T |
|----|-------|
| Use events for decoupling | Tight component coupling |
| Named parameters | Positional arrays |
| #[On] attribute | Old $listeners property |
| dispatchTo for targeted | Global for everything |

→ **See template**: [NestedComponents.php.md](templates/NestedComponents.php.md)
