---
name: components
description: Livewire component creation and rendering
when-to-use: Creating components, properties, rendering
keywords: component, render, class, properties, mount
---

# Livewire Components

## Decision Tree

```
Component type?
├── Multi-file (Class + Blade) → php artisan make:livewire
├── Single-file (Volt functional) → state(), computed()
├── Single-file (Volt class) → new class extends Component
├── Inline in page → @volt directive
└── Full-page route → Route::livewire()
```

## Component Types

| Type | Files | Best For |
|------|-------|----------|
| Class-based | PHP + Blade | Complex logic |
| Volt Functional | Single Blade | Simple components |
| Volt Class | Single Blade | Medium complexity |
| Inline @volt | In any Blade | Quick embeds |

## Artisan Commands

| Command | Creates |
|---------|---------|
| `make:livewire CreatePost` | Class + Blade |
| `make:livewire CreatePost --inline` | Class with inline view |
| `make:livewire post.create` | Volt single-file |

## Rendering Methods

| Method | Usage |
|--------|-------|
| `<livewire:create-post />` | Blade embed |
| `<livewire:posts.create />` | Nested namespace |
| `Route::livewire('/path', Component::class)` | Full-page |
| `Volt::route('/path', 'component-name')` | Volt route |

## Property Types

| Attribute | Purpose |
|-----------|---------|
| `public $name` | Synced with frontend |
| `#[Locked]` | Cannot modify from client |
| `#[Sensitive]` | Hidden in snapshots |
| `#[Computed]` | Cached calculated value |
| `#[Computed(persist: true)]` | Cached between requests |

## Class Structure

| Element | Purpose |
|---------|---------|
| `public $property` | Data binding |
| `public function action()` | Callable from frontend |
| `protected function internal()` | Not callable from frontend |
| `public function render()` | Return view |
| `public function mount()` | Initialization |

## Volt Functional API

| Function | Purpose |
|----------|---------|
| `state(['key' => 'value'])` | Define properties |
| `computed(fn() => ...)` | Computed property |
| `$action = fn() => ...` | Define action |
| `$mount = fn() => ...` | Mount hook |

## Best Practices

| DO | DON'T |
|----|-------|
| Use #[Locked] for IDs | Expose sensitive IDs |
| Keep render() simple | Complex logic in render |
| Use computed for derived data | Recalculate in view |
| Type hint properties | Use mixed types |

→ **See templates**: [BasicComponent.php.md](templates/BasicComponent.php.md), [VoltComponent.blade.md](templates/VoltComponent.blade.md)
