---
name: precognition
description: Laravel Precognition live validation without duplicating backend rules
file-type: markdown
---

# Precognition - Live Validation

## When to Use

| Scenario | Use Precognition? |
|----------|-------------------|
| Form with real-time validation | Yes |
| Reuse backend validation rules | Yes |
| Multi-step wizards | Yes |
| Simple forms | No |

---

## Installation

| Frontend | Package |
|----------|---------|
| Vue | `laravel-precognition-vue` |
| Vue + Inertia | `laravel-precognition-vue-inertia` |
| React | `laravel-precognition-react` |
| React + Inertia | `laravel-precognition-react-inertia` |
| Alpine | `laravel-precognition-alpine` |

---

## Backend Setup

```php
Route::post('/users', fn(StoreUserRequest $r) => ...)
    ->middleware([HandlePrecognitiveRequests::class]);
```

---

## Form Object API

| Method | Purpose |
|--------|---------|
| `form.validate('field')` | Validate single field |
| `form.valid('field')` | Field passed |
| `form.invalid('field')` | Field failed |
| `form.errors` | Error messages |
| `form.hasErrors` | Has any errors |
| `form.processing` | Request in flight |
| `form.validating` | Validation in flight |
| `form.submit()` | Submit form |
| `form.reset()` | Reset form |
| `form.forgetError('field')` | Clear error |

---

## Quick Patterns

### Vue
```vue
<input v-model="form.name" @change="form.validate('name')">
<div v-if="form.invalid('name')">{{ form.errors.name }}</div>
```

### React
```jsx
<input
  value={form.data.name}
  onChange={(e) => form.setData('name', e.target.value)}
  onBlur={() => form.validate('name')}
/>
```

### Alpine
```html
<input x-model="form.name" @change="form.validate('name')">
<template x-if="form.invalid('name')">
  <div x-text="form.errors.name"></div>
</template>
```

---

## Wizard Validation

```js
form.validate({
    only: ['name', 'email'],
    onSuccess: () => nextStep(),
});
```

---

## Customizing Rules

```php
'password' => [
    'required',
    $this->isPrecognitive()
        ? Password::min(8)
        : Password::min(8)->uncompromised(),
],
```

---

## File Uploads

```php
'avatar' => [
    ...$this->isPrecognitive() ? [] : ['required'],
    'image', 'max:2048',
],
```

---

## Testing

```php
$this->withPrecognition()
    ->post('/register', ['name' => 'John'])
    ->assertSuccessfulPrecognition();
```

---

## Best Practices

### DO
- Use `HandlePrecognitiveRequests` middleware
- Debounce with `setValidationTimeout()`
- Skip heavy rules with `isPrecognitive()`

### DON'T
- Include files in precognitive requests
- Count interactions on precognitive requests
