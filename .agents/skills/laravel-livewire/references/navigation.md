---
name: navigation
description: SPA navigation with wire:navigate
when-to-use: Single-page app behavior, prefetch, persist elements
keywords: wire:navigate, persist, SPA, prefetch
---

# SPA Navigation

## Decision Tree

```
Navigation need?
├── SPA behavior → wire:navigate
├── Prefetch on hover → wire:navigate.hover
├── Keep element → @persist
├── Active link style → data-current
└── Full page reload → Normal <a> tag
```

## wire:navigate

| Attribute | Behavior |
|-----------|----------|
| `wire:navigate` | SPA navigation |
| `wire:navigate.hover` | Prefetch on hover |
| No attribute | Normal page load |

## @persist Directive

| Usage | Purpose |
|-------|---------|
| `@persist('id')` | Keep element between pages |
| Audio/video players | Continue playback |
| Complex widgets | Preserve state |
| Must have unique ID | Per-page |

## Active Link Styling

| Selector | Matches |
|----------|---------|
| `data-current` | Current page link |
| `data-current:class` | Add class when active |

## Navigation Events

| Event | When |
|-------|------|
| `livewire:navigate` | Before navigation |
| `livewire:navigating` | Navigation started |
| `livewire:navigated` | Navigation complete |

## JavaScript Listeners

| Pattern | Purpose |
|---------|---------|
| `addEventListener('livewire:navigate')` | Before |
| `addEventListener('livewire:navigated')` | After |
| `event.detail.url` | Target URL |

## Configuration

| Setting | Default |
|---------|---------|
| `navigate.show_progress_bar` | true |
| `navigate.progress_bar_color` | #2299dd |

## Progress Bar

| Config | Purpose |
|--------|---------|
| Show progress | Visual feedback |
| Custom color | Match brand |
| Disable | Set false |

## Best Practices

| DO | DON'T |
|----|-------|
| Use for internal links | External links |
| @persist for players | @persist everything |
| Prefetch hover for common | Prefetch all links |
| Handle navigation events | Ignore JS state |

→ **See also**: [volt.md](volt.md) for Volt routes
