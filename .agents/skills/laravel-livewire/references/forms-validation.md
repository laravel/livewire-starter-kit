---
name: forms-validation
description: Livewire forms, validation rules, form objects
when-to-use: Building forms, validating input, reusable form logic
keywords: validate, rules, form object, error, real-time validation
---

# Forms & Validation

## Decision Tree

```
Form complexity?
├── Simple (few fields) → Inline #[Validate]
├── Reusable → Form Object class
├── Real-time validation → wire:model.blur + rules
├── File upload → WithFileUploads trait
└── Complex logic → Form Object + methods
```

## Validation Attributes

| Attribute | Usage |
|-----------|-------|
| `#[Validate('required')]` | Single rule |
| `#[Validate(['required', 'min:3'])]` | Multiple rules |
| `#[Validate('required', message: 'Required')]` | Custom message |
| `#[Validate('required', as: 'email')]` | Custom name |

## Form Object

| Method | Purpose |
|--------|---------|
| `extends Form` | Base class |
| `public $property` | Form field |
| `validate()` | Run validation |
| `all()` | Get all values |
| `reset()` | Clear form |
| `fill($data)` | Populate form |

## Real-Time Validation

| Pattern | Triggers |
|---------|----------|
| `wire:model.blur` | On field blur |
| `wire:model.live` | On every change |
| `#[Validate]` on property | Auto-validation |

## Error Display

| Method | Usage |
|--------|-------|
| `@error('field')` | Blade directive |
| `$errors->get('field')` | Get errors array |
| `$this->addError('field', 'msg')` | Add manually |
| `$this->resetValidation('field')` | Clear error |

## Form Object Methods

| Method | Purpose |
|--------|---------|
| `store()` | Create new record |
| `update()` | Update existing |
| `setModel($model)` | Initialize from model |

## Validation Hooks

| Hook | Purpose |
|------|---------|
| `rules()` | Define rules method |
| `messages()` | Custom messages |
| `validationAttributes()` | Custom names |

## Error Bag

| Method | Returns |
|--------|---------|
| `$this->getErrorBag()` | Error bag instance |
| `assertHasErrors(['field'])` | Test assertion |
| `assertHasNoErrors()` | Test no errors |

## Best Practices

| DO | DON'T |
|----|-------|
| Use Form Objects | Repeat validation logic |
| wire:model.blur for fields | Live on all fields |
| Custom error messages | Generic messages |
| Validate in action | Skip validation |

→ **See template**: [FormComponent.php.md](templates/FormComponent.php.md)
