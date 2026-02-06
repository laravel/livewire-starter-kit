---
name: testing
description: Testing Livewire components
when-to-use: Unit testing, feature testing, assertions
keywords: test, Livewire::test, Volt::test, assertions
---

# Testing

## Decision Tree

```
Component type?
├── Class component → Livewire::test(Component::class)
├── Volt component → Volt::test('component-name')
├── Full-page → $this->get('/path')->assertSeeLivewire()
├── Events → assertDispatched()
└── File uploads → UploadedFile::fake()
```

## Livewire::test()

| Method | Purpose |
|--------|---------|
| `Livewire::test(Class)` | Create test instance |
| `->set('prop', value)` | Set property |
| `->call('method')` | Call action |
| `->assertSee('text')` | Check output |

## Volt::test()

| Method | Purpose |
|--------|---------|
| `Volt::test('name')` | Test Volt component |
| `Volt::test('path.name')` | Nested path |
| Same assertions | As Livewire::test |

## Property Assertions

| Assertion | Checks |
|-----------|--------|
| `assertSet('prop', value)` | Property equals |
| `assertNotSet('prop', value)` | Property not equals |
| `assertCount('prop', n)` | Array count |

## Validation Assertions

| Assertion | Checks |
|-----------|--------|
| `assertHasErrors(['field'])` | Has error |
| `assertHasErrors(['field' => 'required'])` | Specific rule |
| `assertHasNoErrors()` | No errors |
| `assertHasNoErrors(['field'])` | Specific no error |

## Event Assertions

| Assertion | Checks |
|-----------|--------|
| `assertDispatched('event')` | Event fired |
| `assertDispatched('event', key: val)` | With data |
| `assertNotDispatched('event')` | Not fired |

## View Assertions

| Assertion | Checks |
|-----------|--------|
| `assertSee('text')` | Text visible |
| `assertDontSee('text')` | Text not visible |
| `assertSeeHtml('<div>')` | Raw HTML |
| `assertViewHas('key', value)` | View data |

## Navigation Assertions

| Assertion | Checks |
|-----------|--------|
| `assertRedirect('/path')` | Redirected |
| `assertNoRedirect()` | No redirect |

## File Upload Testing

| Setup | Purpose |
|-------|---------|
| `Storage::fake('public')` | Fake storage |
| `UploadedFile::fake()->image()` | Fake image |
| `assertExists('path')` | File stored |

## HTTP Testing

| Method | Purpose |
|--------|---------|
| `$this->get('/path')` | Visit page |
| `->assertSeeLivewire(Class)` | Component rendered |
| `->assertSeeVolt('name')` | Volt rendered |

## Best Practices

| DO | DON'T |
|----|-------|
| Test behavior, not impl | Test internals |
| Use factories | Hardcode data |
| Test edge cases | Only happy path |
| Assert specific errors | Generic error check |

→ **See template**: [ComponentTest.php.md](templates/ComponentTest.php.md)
