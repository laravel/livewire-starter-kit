# Mejora: Validación de Precios en Tiempo Real

## Resumen

Se agregó validación en tiempo real a los formularios de **creación** y **edición** de precios para detectar conflictos antes de guardar.

## Problema

Cuando se intentaba crear o editar un precio activo para una parte que ya tenía un precio activo del mismo tipo de estación, el sistema no mostraba ningún error hasta intentar guardar, y el error del Observer no era claro.

## Solución Implementada

### 1. Validación en Tiempo Real

**Archivos**: 
- `app/Livewire/Admin/Prices/PriceCreate.php`
- `app/Livewire/Admin/Prices/PriceEdit.php`

Se agregaron:

- **Propiedades públicas**:
  - `$validation_message`: Mensaje de error a mostrar
  - `$has_conflict`: Bandera que indica si hay conflicto

- **Métodos de validación**:
  - `updatedPartId()`: Se ejecuta cuando se selecciona una parte
  - `updatedWorkstationType()`: Se ejecuta cuando se cambia el tipo de estación
  - `updatedActive()`: Se ejecuta cuando se cambia el estado activo/inactivo
  - `checkForConflicts()`: Verifica si existe un precio activo conflictivo

### 2. Diferencia entre Create y Edit

**En PriceCreate**:
```php
$existingPrice = Price::where('part_id', $this->part_id)
    ->where('workstation_type', $this->workstation_type)
    ->where('active', true)
    ->first();
```

**En PriceEdit**:
```php
$existingPrice = Price::where('part_id', $this->part_id)
    ->where('workstation_type', $this->workstation_type)
    ->where('active', true)
    ->where('id', '!=', $this->price->id)  // ← Excluir el precio actual
    ->first();
```

### 3. Prevención de Guardado

En ambos métodos `savePrice()` y `updatePrice()`:

```php
// Validar primero si hay conflictos
if ($this->has_conflict && $this->active) {
    $this->addError('part_id', $this->validation_message);
    return;
}
```

### 4. UI Mejorada

**Archivos**: 
- `resources/views/livewire/admin/prices/price-create.blade.php`
- `resources/views/livewire/admin/prices/price-edit.blade.php`

Se agregó un mensaje de alerta visual que aparece automáticamente cuando hay conflicto:

```blade
@if($has_conflict)
    <div class="mt-2 p-3 rounded-lg bg-red-50 border border-red-200">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-red-500 mr-2">...</svg>
            <span class="text-sm text-red-700">{{ $validation_message }}</span>
        </div>
    </div>
@endif
```

## Comportamiento

### Escenario 1: Crear - Seleccionar Parte con Precio Existente

1. Usuario selecciona una parte
2. Sistema verifica inmediatamente si existe precio activo del tipo seleccionado
3. Si existe, muestra mensaje de error en rojo
4. Botón "Guardar" está habilitado pero al hacer clic muestra error

### Escenario 2: Editar - Cambiar Tipo de Estación

1. Usuario edita un precio existente
2. Usuario cambia de "Mesa de Trabajo" a "Máquina"
3. Sistema verifica si existe **otro** precio activo tipo "Máquina" (excluyendo el actual)
4. Muestra/oculta mensaje según corresponda

### Escenario 3: Editar - Activar Precio

1. Usuario edita un precio inactivo
2. Usuario marca "Precio activo"
3. Sistema verifica si existe otro precio activo del mismo tipo
4. Muestra mensaje si hay conflicto

### Escenario 4: Desactivar Precio

1. Usuario desmarca "Precio activo"
2. Sistema oculta el mensaje de conflicto
3. Permite guardar el precio como inactivo

## Mensajes de Error

### Mensaje de Conflicto (Create)

```
Ya existe un precio activo para esta parte con tipo de estación [Tipo]. 
Debes desactivar el precio existente primero o crear este precio como inactivo.
```

### Mensaje de Conflicto (Edit)

```
Ya existe otro precio activo para esta parte con tipo de estación [Tipo]. 
Debes desactivar el precio existente primero o guardar este precio como inactivo.
```

### Soluciones Sugeridas

El mensaje indica dos opciones:

1. **Desactivar el precio existente**: Ir a la lista de precios y desactivar el precio conflictivo
2. **Crear/Guardar como inactivo**: Desmarcar "Precio activo" y guardar

## Beneficios

✅ **Feedback inmediato**: El usuario ve el error antes de intentar guardar
✅ **Mensaje claro**: Explica exactamente qué está mal y cómo solucionarlo
✅ **Previene errores**: No permite guardar si hay conflicto
✅ **Mejor UX**: Validación en tiempo real sin necesidad de submit
✅ **Funciona en Create y Edit**: Consistencia en ambos formularios

## Testing

### Probar Create

1. Ir a `/admin/prices/create`
2. Seleccionar una parte que ya tenga precio activo tipo "Mesa de Trabajo"
3. Verificar que aparece el mensaje de error inmediatamente
4. Cambiar a tipo "Máquina" (si no tiene precio de ese tipo)
5. Verificar que el mensaje desaparece
6. Desmarcar "Precio activo"
7. Verificar que el mensaje desaparece y permite guardar

### Probar Edit

1. Ir a `/admin/prices/{id}/edit` de un precio activo
2. Cambiar el tipo de estación a uno que ya tenga otro precio activo
3. Verificar que aparece el mensaje de error
4. Cambiar a un tipo sin conflicto
5. Verificar que el mensaje desaparece
6. Intentar guardar y verificar que funciona

## Archivos Modificados

- ✅ `app/Livewire/Admin/Prices/PriceCreate.php`
- ✅ `app/Livewire/Admin/Prices/PriceEdit.php`
- ✅ `resources/views/livewire/admin/prices/price-create.blade.php`
- ✅ `resources/views/livewire/admin/prices/price-edit.blade.php`
