---
name: loading-states
description: Loading indicators and lazy loading
when-to-use: Show loading states, lazy components, placeholders
keywords: wire:loading, lazy, placeholder, skeleton
---

# Loading States

## Decision Tree

```
Loading type?
├── Show while loading → wire:loading
├── Hide while loading → wire:loading.remove
├── Disable element → wire:loading.attr="disabled"
├── Add class → wire:loading.class="opacity-50"
├── Target action → wire:target="save"
└── Lazy component → #[Lazy] attribute
```

## wire:loading Modifiers

| Modifier | Effect |
|----------|--------|
| `wire:loading` | Show element |
| `wire:loading.remove` | Hide element |
| `wire:loading.class="..."` | Add class |
| `wire:loading.class.remove="..."` | Remove class |
| `wire:loading.attr="disabled"` | Add attribute |
| `wire:loading.delay` | 200ms delay |
| `wire:loading.delay.long` | 500ms delay |

## wire:target

| Syntax | Targets |
|--------|---------|
| `wire:target="save"` | Specific action |
| `wire:target="save, delete"` | Multiple actions |
| `wire:target="form.title"` | Property update |

## #[Lazy] Component

| Feature | Purpose |
|---------|---------|
| `#[Lazy]` | Load on demand |
| `placeholder()` | Custom placeholder |
| `<livewire:comp lazy />` | Enable lazy |
| `#[Lazy(isolate: false)]` | Disable bundling |

## Placeholder Method

| Return Type | Usage |
|-------------|-------|
| String HTML | Inline placeholder |
| View | `view('placeholder')` |
| Blade component | `<x-skeleton />` |

## wire:intersect

| Modifier | Triggers |
|----------|----------|
| `wire:intersect` | When visible |
| `wire:intersect.once` | Only once |
| `wire:intersect.full` | Fully visible |

## Skeleton Patterns

| Pattern | Use Case |
|---------|----------|
| Animated pulse | Loading content |
| Shimmer effect | Cards, lists |
| Spinner | Buttons, small areas |
| Progress bar | File uploads |

## Loading Button Pattern

| State | Display |
|-------|---------|
| Normal | "Save" |
| Loading | Spinner + "Saving..." |
| Disabled | Prevent double-click |

## Best Practices

| DO | DON'T |
|----|-------|
| Use wire:target | Global loading for all |
| Add delay for fast ops | Flash on quick actions |
| Disable buttons | Allow double submit |
| Use #[Lazy] for heavy | Lazy everything |

→ **See template**: [LoadingStates.blade.md](templates/LoadingStates.blade.md)
