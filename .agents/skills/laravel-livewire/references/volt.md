---
name: volt
description: Volt single-file components
when-to-use: Simple components, functional API, quick pages
keywords: volt, state, computed, functional, single-file
---

# Volt Components

## Decision Tree

```
Volt style?
├── Functional API → state(), computed(), $action
├── Class-based → new class extends Component
├── Inline in page → @volt directive
├── Route → Volt::route()
└── Mixed → PHP + Blade in one file
```

## Functional API

| Function | Purpose |
|----------|---------|
| `state(['key' => val])` | Define properties |
| `computed(fn() => ...)` | Computed property |
| `$action = fn() => ...` | Define action |
| `$mount = fn() => ...` | Mount hook |
| `$updated = fn($prop) => ...` | Updated hook |

## State Definition

| Pattern | Usage |
|---------|-------|
| `state(['count' => 0])` | With default |
| `state(['user'])` | No default (null) |
| `state()->reactive()` | Reactive to parent |
| `state()->modelable()` | Two-way binding |

## Class-Based Volt

| Element | Same as Class Component |
|---------|------------------------|
| `new class extends Component` | Inline class |
| Properties | `public $prop` |
| Methods | `public function method()` |
| Lifecycle | mount(), updated(), etc |

## @volt Directive

| Usage | Purpose |
|-------|---------|
| `@volt('name')` | Start Volt block |
| `@endvolt` | End Volt block |
| In any Blade | Inline component |

## Volt Routes

| Method | Purpose |
|--------|---------|
| `Volt::route('/path', 'name')` | Define route |
| `->middleware([...])` | Add middleware |
| `->name('route.name')` | Name route |

## Accessing $this

| In Functional | Access |
|---------------|--------|
| In actions | `$this->property` |
| In computed | `$this->property` |
| In hooks | `$this->property` |

## Computed in Functional

| Syntax | Usage |
|--------|-------|
| `$doubleCount = computed(...)` | Define |
| `$this->doubleCount` | Access in PHP |
| `{{ $this->doubleCount }}` | Access in Blade |

## With Dependencies

| Pattern | Usage |
|---------|-------|
| `$action = function(Service $s)` | Inject in action |
| Auto-resolved | From container |

## Best Practices

| DO | DON'T |
|----|-------|
| Volt for simple pages | Complex logic in Volt |
| Functional for few props | Many computed = Class |
| @volt for inline embeds | Overuse inline |
| Name Volt routes | Anonymous routes |

→ **See template**: [VoltComponent.blade.md](templates/VoltComponent.blade.md)
