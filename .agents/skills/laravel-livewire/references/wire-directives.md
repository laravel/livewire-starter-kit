---
name: wire-directives
description: Livewire wire:* directives for data binding and actions
when-to-use: Binding data, handling events, loading states
keywords: wire:model, wire:click, wire:submit, wire:loading, wire:key
---

# Wire Directives

## Decision Tree

```
What to bind?
├── Input value → wire:model
├── Click action → wire:click
├── Form submit → wire:submit
├── Loading state → wire:loading
├── Keyboard → wire:keydown
├── Unique ID → wire:key
└── Ignore updates → wire:ignore
```

## wire:model Modifiers

| Modifier | Behavior |
|----------|----------|
| `wire:model` | Deferred (on action) |
| `wire:model.live` | Real-time sync |
| `wire:model.blur` | Sync on blur |
| `wire:model.live.debounce.500ms` | Debounced real-time |
| `wire:model.live.throttle.1s` | Throttled sync |
| `wire:model.fill` | Ignore initial value |

## Event Directives

| Directive | Triggers On |
|-----------|-------------|
| `wire:click` | Click |
| `wire:submit` | Form submit |
| `wire:keydown` | Key press |
| `wire:keydown.enter` | Enter key |
| `wire:change` | Input change |
| `wire:input` | Input event |

## Event Modifiers

| Modifier | Effect |
|----------|--------|
| `.prevent` | preventDefault() |
| `.stop` | stopPropagation() |
| `.self` | Only if target is element |
| `.window` | Listen on window |
| `.document` | Listen on document |

## wire:loading

| Syntax | Shows When |
|--------|------------|
| `wire:loading` | Any loading |
| `wire:loading.remove` | Hide when loading |
| `wire:loading.attr="disabled"` | Add attribute |
| `wire:loading.class="opacity-50"` | Add class |
| `wire:target="save"` | Specific action |

## wire:key

| Usage | Purpose |
|-------|---------|
| `wire:key="item-{{ $id }}"` | Unique ID in loops |
| Required in @foreach | Proper diffing |
| Must be unique | Per-loop iteration |

## wire:ignore

| Syntax | Behavior |
|--------|----------|
| `wire:ignore` | Ignore element updates |
| `wire:ignore.self` | Ignore only this element |
| Use for | Third-party JS widgets |

## wire:poll

| Syntax | Interval |
|--------|----------|
| `wire:poll` | 2 seconds (default) |
| `wire:poll.5s` | 5 seconds |
| `wire:poll.visible` | Only when visible |
| `wire:poll="method"` | Call specific method |

## wire:navigate

| Syntax | Behavior |
|--------|----------|
| `wire:navigate` | SPA navigation |
| `wire:navigate.hover` | Prefetch on hover |

## Best Practices

| DO | DON'T |
|----|-------|
| Use debounce for search | Live without debounce |
| Use blur for validation | Live for every field |
| Always use wire:key in loops | Forget keys in @foreach |
| Target specific actions | Global loading states |

→ **See template**: [FormComponent.php.md](templates/FormComponent.php.md)
