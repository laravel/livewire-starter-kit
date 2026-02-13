---
name: lifecycle
description: Livewire component lifecycle hooks
when-to-use: Initialization, property updates, request handling
keywords: mount, hydrate, dehydrate, updating, updated, boot
---

# Lifecycle Hooks

## Decision Tree

```
When to hook?
├── Initial render only → mount()
├── Every request → boot()
├── Subsequent requests → hydrate()
├── Before property update → updating()
├── After property update → updated()
├── Before serialization → dehydrate()
└── Handle exceptions → exception()
```

## Hook Order

| # | Hook | When Called |
|---|------|-------------|
| 1 | `boot()` | Every request |
| 2 | `mount($params)` | Initial only |
| 3 | `hydrate()` | Subsequent only |
| 4 | `updating($prop, $val)` | Before update |
| 5 | `updated($prop, $val)` | After update |
| 6 | `render()` | Always |
| 7 | `dehydrate()` | End of request |

## Property-Specific Hooks

| Hook | Called When |
|------|-------------|
| `updatingName($value)` | Before $name changes |
| `updatedName($value)` | After $name changes |
| `updatingItems($value, $key)` | Array with key |

## mount() Parameters

| Source | Injection |
|--------|-----------|
| Route params | Auto-injected |
| Parent props | Auto-injected |
| Type-hinted | Route model binding |

## hydrate() Use Cases

| Use Case | Example |
|----------|---------|
| Restore non-serializable | `$this->dto = new Dto($this->data)` |
| Init services | `$this->service = app(Service::class)` |
| Restore state | Rebuild computed state |

## dehydrate() Use Cases

| Use Case | Example |
|----------|---------|
| Convert objects | `$this->dto = $this->dto->toArray()` |
| Cleanup | Remove non-serializable |
| Prepare state | For next request |

## Form Object Hooks

| Hook | Purpose |
|------|---------|
| `updating()` | Before form property update |
| `updated()` | After form property update |
| `updatingTitle($value)` | Specific property |

## exception() Hook

| Usage | Purpose |
|-------|---------|
| `exception($e, $stop)` | Handle errors |
| `$stop()` | Prevent propagation |
| Custom handling | Flash messages |

## Best Practices

| DO | DON'T |
|----|-------|
| Use mount for init | Query in render |
| Type hint in mount | Manual route binding |
| Use updating for transform | Modify in accessor |
| Clean in dehydrate | Store non-serializable |

→ **See template**: [BasicComponent.php.md](templates/BasicComponent.php.md)
