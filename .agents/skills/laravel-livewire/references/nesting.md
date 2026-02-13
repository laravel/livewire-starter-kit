---
name: nesting
description: Parent-child component communication
when-to-use: Nested components, passing data, two-way binding
keywords: parent, child, reactive, modelable, props
---

# Nesting Components

## Decision Tree

```
Communication direction?
├── Parent → Child → Props (normal or #[Reactive])
├── Child → Parent → Events or $parent
├── Two-way binding → #[Modelable]
├── Child updates parent → dispatch() event
└── Access parent method → $parent.method()
```

## Passing Props

| Syntax | Type |
|--------|------|
| `:prop="$value"` | Dynamic value |
| `prop="string"` | Static string |
| `:wire:key="$id"` | Required in loops |

## #[Reactive] Props

| Behavior | When |
|----------|------|
| Auto-update child | Parent prop changes |
| No explicit sync | Automatic |
| Read-only in child | Cannot modify |

## #[Modelable] Props

| Behavior | When |
|----------|------|
| Two-way binding | `wire:model` on component |
| Child can update | Parent receives changes |
| Like input binding | Component as input |

## Accessing Parent

| Method | Usage |
|--------|-------|
| `$parent.property` | Read parent property |
| `$parent.method()` | Call parent method |
| `wire:click="$parent.remove"` | In Blade |

## Event Communication

| Direction | Method |
|-----------|--------|
| Child → Parent | `dispatch('event')` |
| Parent listens | `#[On('event')]` |
| Targeted | `dispatchTo('parent', 'event')` |

## wire:key Rules

| Rule | Reason |
|------|--------|
| Required in loops | Proper DOM diffing |
| Must be unique | Per iteration |
| Stable ID | Don't use index alone |
| Include model ID | `wire:key="item-{{ $id }}"` |

## Props vs Events

| Use Props | Use Events |
|-----------|------------|
| Data down | Actions up |
| Configuration | Notifications |
| Initial state | State changes |
| Read-only | Side effects |

## Computed in Parent

| Pattern | Purpose |
|---------|---------|
| Pass computed result | Child receives data |
| Child #[Reactive] | Auto-updates |

## Best Practices

| DO | DON'T |
|----|-------|
| Use wire:key always | Forget keys in loops |
| Events for actions | Direct parent mutation |
| #[Reactive] for sync | Manual refresh |
| #[Modelable] for forms | Complex event chains |

→ **See template**: [NestedComponents.php.md](templates/NestedComponents.php.md)
