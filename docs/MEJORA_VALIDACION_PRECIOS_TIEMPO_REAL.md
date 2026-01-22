# Mejora: Validación de Precios en Tiempo Real

## Resumen

Se agregó validación en tiempo real a los formularios de **creación** y **edición** de precios para detectar conflictos antes de guardar.

## Regla de Negocio Crítica

**⚠️ UNA PARTE SOLO PUEDE TENER UN PRECIO ACTIVO EN TOTAL**

Independientemente del tipo de estación de trabajo. Si una parte tiene un precio activo para "Mesa de Trabajo", NO puede tener otro precio activo para "Máquina" o "Semi-Automático".

## Problema

Cuando se intentaba crear o editar un precio activo para una parte que ya tenía un precio activo, el sistema no mostraba ningún error hasta intentar guardar, y el error del Observer no era claro.

## Solución Implementada

### 1. Validación en Tiempo Real

**Archivos**: 
- `app/Livewire/Admin/Prices/PriceCreate.php`
- `app/Livewire/Admin/Prices/PriceEdit.php`

Se agregaron:

- **Propiedades públicas**:
  - `$validation_message`: Mensaje de error cuando hay conflicto
  - `$has_conflict`: Bandera que indica si hay conflicto
  - `$info_message`: Mensaje informativo sobre precios existentes
  - `$has_existing_prices`: Bandera para indicar si hay precios existentes

- **Métodos de validación**:
  - `updatedPartId()`: Se ejecuta cuando se selecciona una parte
  - `updatedActive()`: Se ejecuta cuando se cambia el estado activo/inactivo
  - `checkForConflicts()`: Verifica si existe un precio activo conflictivo

### 2. Diferencia entre Create y Edit

**En PriceCreate**:
```php
// Verificar si la parte tiene ALGÚN precio activo (de cualquier tipo)
$existingActivePrice = Price::where('part_id', $this->part_id)
    ->where('active', true)
    ->first();
```

**En PriceEdit**:
```php
// Verificar si la parte tiene ALGÚN precio activo (de cualquier tipo)
// Excluir el precio actual que se está editando
$existingActivePrice = Price::where('part_id', $this->part_id)
    ->where('active', true)
    ->where('id', '!=', $this->price->id)
    ->first();
```

### 3. Mensajes Informativos

Además del mensaje de error, se muestra información sobre precios existentes:

```php
// Mostrar información de precios existentes (activos o inactivos)
$allPrices = Price::where('part_id', $this->part_id)->get();

if ($allPrices->isNotEmpty()) {
    $this->has_existing_prices = true;
    $activePrices = $allPrices->where('active', true);
    $inactivePrices = $allPrices->where('active', false);
    
    $info = [];
    if ($activePrices->isNotEmpty()) {
        $types = $activePrices->pluck('workstation_type')->map(...)->join(', ');
        $info[] = "Activos: {$types}";
    }
    if ($inactivePrices->isNotEmpty()) {
        $types = $inactivePrices->pluck('workstation_type')->map(...)->join(', ');
        $info[] = "Inactivos: {$types}";
    }
    
    $this->info_message = "Esta parte tiene precios registrados - " . implode(' | ', $info);
}
```

### 4. Prevención de Guardado

En ambos métodos `savePrice()` y `updatePrice()`:

```php
// Validar primero si hay conflictos
if ($this->has_conflict && $this->active) {
    $this->addError('part_id', $this->validation_message);
    return;
}
```

### 5. UI Mejorada

**Archivos**: 
- `resources/views/livewire/admin/prices/price-create.blade.php`
- `resources/views/livewire/admin/prices/price-edit.blade.php`

Se agregaron dos tipos de alertas:

**Alerta de Error (Roja)** - Cuando hay conflicto:
```blade
@if($has_conflict)
    <div class="mt-2 p-3 rounded-lg bg-red-50 border border-red-200">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-red-500 mr-2">...</svg>
            <span class="text-sm text-red-700">{{ $validation_message }}</span>
        </div>
    </div>
@elseif($has_existing_prices && $info_message)
```

**Alerta Informativa (Azul)** - Cuando hay precios existentes sin conflicto:
```blade
    <div class="mt-2 p-3 rounded-lg bg-blue-50 border border-blue-200">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-500 mr-2">...</svg>
            <span class="text-sm text-blue-700">{{ $info_message }}</span>
        </div>
    </div>
@endif
```

**Actualización en Tiempo Real**:
- Campo de parte: `wire:model.live="part_id"`
- Checkbox activo: `wire:model.live="active"`

## Comportamiento

### Escenario 1: Crear - Seleccionar Parte con Precio Activo

1. Usuario selecciona una parte que tiene un precio activo (cualquier tipo)
2. Sistema muestra mensaje de error en rojo inmediatamente
3. Usuario puede:
   - Desmarcar "Precio activo" → mensaje desaparece, puede guardar como inactivo
   - Seleccionar otra parte
   - Cancelar

### Escenario 2: Crear - Seleccionar Parte con Precios Inactivos

1. Usuario selecciona una parte que solo tiene precios inactivos
2. Sistema muestra mensaje informativo en azul
3. Usuario puede crear el precio sin restricciones

### Escenario 3: Editar - Activar Precio

1. Usuario edita un precio inactivo
2. Usuario marca "Precio activo"
3. Sistema verifica si existe otro precio activo (de cualquier tipo)
4. Si existe, muestra mensaje de error
5. Usuario debe desmarcar "Precio activo" o desactivar el otro precio primero

### Escenario 4: Editar - Cambiar Parte

1. Usuario edita un precio y cambia la parte
2. Sistema verifica inmediatamente si la nueva parte tiene precio activo
3. Muestra mensaje según corresponda

## Mensajes

### Mensaje de Conflicto (Create)

```
Esta parte ya tiene un precio activo (Tipo: Mesa de Trabajo). 
Solo puede haber un precio activo por parte. 
Debes desactivar el precio existente primero o crear este precio como inactivo.
```

### Mensaje de Conflicto (Edit)

```
Esta parte ya tiene otro precio activo (Tipo: Máquina). 
Solo puede haber un precio activo por parte. 
Debes desactivar el precio existente primero o guardar este precio como inactivo.
```

### Mensaje Informativo

```
Esta parte tiene precios registrados - Activos: Mesa de Trabajo | Inactivos: Máquina, Semi-Automático
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
✅ **Información contextual**: Muestra todos los precios existentes para la parte
✅ **Regla de negocio clara**: Un precio activo por parte, sin excepciones

## Testing

### Probar Create

1. Ir a `/admin/prices/create`
2. Seleccionar una parte que ya tenga precio activo (cualquier tipo)
3. ✅ Verificar que aparece mensaje de error rojo inmediatamente
4. Desmarcar "Precio activo"
5. ✅ Verificar que el mensaje de error desaparece
6. ✅ Verificar que aparece mensaje informativo azul
7. Guardar como inactivo
8. ✅ Verificar que se guarda correctamente

### Probar Edit

1. Ir a `/admin/prices/{id}/edit` de un precio inactivo
2. Marcar "Precio activo"
3. ✅ Si la parte tiene otro precio activo, debe mostrar error
4. Desmarcar "Precio activo"
5. ✅ Verificar que el mensaje desaparece
6. Cambiar a una parte sin precio activo
7. Marcar "Precio activo"
8. ✅ Verificar que NO muestra error y permite guardar

### Probar Información

1. Seleccionar una parte con múltiples precios (activos e inactivos)
2. ✅ Verificar que muestra información completa de todos los precios
3. ✅ Verificar que distingue entre activos e inactivos

## Archivos Modificados

- ✅ `app/Livewire/Admin/Prices/PriceCreate.php`
- ✅ `app/Livewire/Admin/Prices/PriceEdit.php`
- ✅ `resources/views/livewire/admin/prices/price-create.blade.php`
- ✅ `resources/views/livewire/admin/prices/price-edit.blade.php`

## Estado

✅ **COMPLETADO** - La validación en tiempo real está funcionando correctamente con la regla de negocio de un precio activo por parte.
