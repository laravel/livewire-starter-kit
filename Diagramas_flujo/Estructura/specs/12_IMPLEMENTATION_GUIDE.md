# Guía de Implementación: Validación de part_id en Standards

**Documento complementario de:** `12_standards_unique_part_validation_analysis.md`

## Decisión: ¿Qué opción elegir?

Lee el análisis completo en el documento principal. Aquí un resumen rápido:

### OPCIÓN A: UNIQUE Simple
- Un estándar por parte, sin historial
- Implementación: 1 migración
- Tiempo: 5 minutos

### OPCIÓN B: Validación en App
- Múltiples estándares (historial), solo uno activo
- Implementación: 1 migración + 1 rule + actualizar 2 components
- Tiempo: 30-45 minutos

---

## Implementación OPCIÓN A (UNIQUE Simple)

### Paso 1: Activar la migración

```bash
# Renombrar archivo
cd database/migrations
mv 2026_01_13_OPTION_A_add_unique_part_id_to_standards.php.example \
   2026_01_13_add_unique_part_id_to_standards.php
```

### Paso 2: Revisar y ajustar lógica de limpieza

Abre la migración y revisa líneas 45-48:
```php
// ELIMINAR todos los demás (HARD DELETE, no soft delete)
DB::table('standards')
    ->where('part_id', $partId)
    ->where('id', '!=', $keepId)
    ->delete();
```

**IMPORTANTE:** Por defecto mantiene el más reciente. Si necesitas otro criterio, ajusta.

### Paso 3: Ejecutar migración

```bash
php artisan migrate
```

Salida esperada:
```
Part ID 18: Kept standard #17, deleted others
Part ID 22: Kept standard #21, deleted others
Migration: 2026_01_13_add_unique_part_id_to_standards
Migrated:  2026_01_13_add_unique_part_id_to_standards
```

### Paso 4: Verificar

```bash
php artisan tinker
```

```php
// Debe devolver 0 (no hay duplicados)
Standard::select('part_id')
    ->groupBy('part_id')
    ->havingRaw('COUNT(*) > 1')
    ->count();

// Probar crear duplicado (debe fallar con error DB)
Standard::create([
    'part_id' => 22,
    'units_per_hour' => 100,
    'active' => true
]); // → Error: Duplicate entry
```

### Paso 5: Opcional - Simplificar código

Si elegiste OPCIÓN A, estos campos ya no tienen sentido:

**1. Eliminar campo `active`:**
```bash
php artisan make:migration remove_active_from_standards
```

```php
public function up(): void
{
    Schema::table('standards', function (Blueprint $table) {
        $table->dropColumn('active');
    });
}
```

**2. Eliminar campo `effective_date`:**
(similar al anterior)

**3. Actualizar modelo `Standard.php`:**
- Eliminar scopes `scopeActive()` y `scopeInactive()`
- Eliminar método `getStats()` o ajustar para no contar inactivos
- Eliminar constantes `STATUS_ACTIVE`, `STATUS_INACTIVE`

**4. Actualizar componentes Livewire:**
- `StandardCreate.php`: Eliminar campo `active`
- `StandardEdit.php`: Eliminar campo `active`
- `StandardList.php`: Eliminar filtros por activo/inactivo

### Paso 6: Testing

```bash
php artisan test --filter=StandardTest
```

Actualiza los tests para reflejar la nueva restricción:
```php
// tests/Feature/StandardTest.php

public function test_cannot_create_duplicate_part_id()
{
    $part = Part::factory()->create();

    Standard::factory()->create(['part_id' => $part->id]);

    $this->expectException(QueryException::class);
    Standard::factory()->create(['part_id' => $part->id]);
}
```

---

## Implementación OPCIÓN B (Validación en App)

### Paso 1: Activar la migración

```bash
# Renombrar archivo
cd database/migrations
mv 2026_01_13_OPTION_B_add_unique_active_standard_index.php.example \
   2026_01_13_add_unique_active_standard_index.php
```

### Paso 2: Ejecutar migración

```bash
php artisan migrate
```

Salida esperada:
```
Part ID 18: Kept standard #17 active, deactivated 1 others
Part ID 22: Kept standard #21 active, deactivated 2 others
Migration: 2026_01_13_add_unique_active_standard_index
Migrated:  2026_01_13_add_unique_active_standard_index
```

### Paso 3: Crear la Rule

```bash
# Renombrar archivo
mv app/Rules/UniqueActiveStandard.php.example \
   app/Rules/UniqueActiveStandard.php
```

Verifica que el namespace sea correcto:
```php
namespace App\Rules;
```

### Paso 4: Actualizar StandardCreate.php

```php
// app/Livewire/Admin/Standards/StandardCreate.php

use App\Rules\UniqueActiveStandard;

protected function rules(): array
{
    return [
        'part_id' => 'required|exists:parts,id',
        'active' => [
            'boolean',
            new UniqueActiveStandard($this->part_id)
        ],
        'units_per_hour' => 'required|integer|min:1',
        // ... resto de campos
    ];
}
```

### Paso 5: Actualizar StandardEdit.php

```php
// app/Livewire/Admin/Standards/StandardEdit.php

use App\Rules\UniqueActiveStandard;

protected function rules(): array
{
    return [
        'part_id' => 'required|exists:parts,id',
        'active' => [
            'boolean',
            new UniqueActiveStandard(
                $this->standard->part_id,
                $this->standard->id  // ← Excluir el estándar actual
            )
        ],
        'units_per_hour' => 'required|integer|min:1',
        // ... resto de campos
    ];
}
```

### Paso 6: Agregar helper al modelo (opcional pero recomendado)

```php
// app/Models/Standard.php

/**
 * Verifica si esta parte ya tiene un estándar activo
 *
 * @param int $partId
 * @param int|null $exceptId ID a excluir (para edición)
 * @return bool
 */
public static function hasActiveStandard(int $partId, ?int $exceptId = null): bool
{
    $query = self::where('part_id', $partId)
        ->where('active', true);

    if ($exceptId) {
        $query->where('id', '!=', $exceptId);
    }

    return $query->exists();
}

/**
 * Obtiene el estándar activo para una parte
 *
 * @param int $partId
 * @return Standard|null
 */
public static function getActiveStandard(int $partId): ?Standard
{
    return self::where('part_id', $partId)
        ->where('active', true)
        ->first();
}

/**
 * Obtiene todos los estándares inactivos para una parte (historial)
 *
 * @param int $partId
 * @return \Illuminate\Database\Eloquent\Collection
 */
public static function getStandardHistory(int $partId)
{
    return self::where('part_id', $partId)
        ->where('active', false)
        ->orderBy('effective_date', 'desc')
        ->get();
}
```

### Paso 7: Actualizar UI para mostrar historial (opcional)

En `StandardShow.php` o `StandardList.php`, agrega una sección para ver estándares inactivos:

```php
// app/Livewire/Admin/Standards/StandardShow.php

public function render()
{
    $history = Standard::getStandardHistory($this->standard->part_id);

    return view('livewire.admin.standards.standard-show', [
        'standard' => $this->standard,
        'history' => $history
    ]);
}
```

```blade
{{-- resources/views/livewire/admin/standards/standard-show.blade.php --}}

@if($history->isNotEmpty())
    <div class="mt-6">
        <h3 class="text-lg font-semibold mb-4">Historial de Estándares</h3>
        <div class="space-y-2">
            @foreach($history as $oldStandard)
                <div class="p-3 bg-gray-50 rounded border">
                    <div class="flex justify-between">
                        <span class="font-medium">
                            {{ $oldStandard->units_per_hour }} units/hr
                        </span>
                        <span class="text-sm text-gray-600">
                            Efectivo: {{ $oldStandard->effective_date?->format('Y-m-d') ?? 'N/A' }}
                        </span>
                    </div>
                    @if($oldStandard->description)
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $oldStandard->description }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
```

### Paso 8: Testing

```bash
php artisan make:test StandardUniqueActiveTest
```

```php
// tests/Feature/StandardUniqueActiveTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Part;
use App\Models\Standard;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StandardUniqueActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_multiple_standards_if_only_one_active()
    {
        $part = Part::factory()->create();

        // Crear primer estándar activo
        $standard1 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true
        ]);

        // Crear segundo estándar INACTIVO (debe funcionar)
        $standard2 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => false
        ]);

        $this->assertDatabaseCount('standards', 2);
    }

    public function test_cannot_create_two_active_standards_for_same_part()
    {
        $part = Part::factory()->create();

        // Crear primer estándar activo
        Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true
        ]);

        // Intentar crear segundo estándar activo
        // Debe fallar en la validación
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $data = [
            'part_id' => $part->id,
            'units_per_hour' => 100,
            'active' => true
        ];

        // Simular request desde Livewire component
        // ... (ajustar según tu implementación)
    }

    public function test_can_deactivate_and_create_new_active()
    {
        $part = Part::factory()->create();

        // Crear estándar activo
        $oldStandard = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true,
            'units_per_hour' => 100
        ]);

        // Desactivar el viejo
        $oldStandard->update(['active' => false]);

        // Crear nuevo estándar activo (debe funcionar)
        $newStandard = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true,
            'units_per_hour' => 150
        ]);

        $this->assertDatabaseCount('standards', 2);
        $this->assertEquals(false, $oldStandard->fresh()->active);
        $this->assertEquals(true, $newStandard->fresh()->active);
    }
}
```

Ejecutar tests:
```bash
php artisan test --filter=StandardUniqueActiveTest
```

### Paso 9: Verificación manual

```bash
php artisan tinker
```

```php
// Caso 1: Crear estándar inactivo (debe funcionar)
$part = Part::first();
Standard::create([
    'part_id' => $part->id,
    'units_per_hour' => 100,
    'active' => false
]);
// ✅ Funciona

// Caso 2: Crear otro inactivo (debe funcionar)
Standard::create([
    'part_id' => $part->id,
    'units_per_hour' => 200,
    'active' => false
]);
// ✅ Funciona

// Caso 3: Activar uno
$std = Standard::where('part_id', $part->id)->first();
$std->update(['active' => true]);
// ✅ Funciona

// Caso 4: Intentar activar otro (debe fallar en validación Livewire)
// Esto SOLO se valida en Livewire, no directamente con Eloquent
// Para probarlo, usa la interfaz web o un test de Feature

// Verificar estado
Standard::where('part_id', $part->id)->get(['id', 'active', 'units_per_hour']);
```

---

## Manejo de Datos Existentes

### Verificar estado actual

```bash
php artisan tinker
```

```php
// Ver duplicados actuales
$duplicates = Standard::select('part_id', DB::raw('COUNT(*) as count'))
    ->where('active', true)
    ->groupBy('part_id')
    ->havingRaw('COUNT(*) > 1')
    ->get();

foreach ($duplicates as $dup) {
    $part = Part::find($dup->part_id);
    echo "Part: {$part->number} ({$dup->count} activos)\n";

    Standard::where('part_id', $dup->part_id)
        ->where('active', true)
        ->get()
        ->each(function($s) {
            echo "  - Std #{$s->id}: {$s->units_per_hour} u/hr, created: {$s->created_at}\n";
        });
}
```

### Limpieza manual (si no usas las migraciones)

**Para OPCIÓN A:**
```php
// Mantener el más reciente, eliminar los demás
$duplicates = Standard::select('part_id')
    ->groupBy('part_id')
    ->havingRaw('COUNT(*) > 1')
    ->pluck('part_id');

foreach ($duplicates as $partId) {
    $keep = Standard::where('part_id', $partId)
        ->orderBy('created_at', 'desc')
        ->first();

    Standard::where('part_id', $partId)
        ->where('id', '!=', $keep->id)
        ->delete(); // HARD DELETE

    echo "Part {$partId}: Kept #{$keep->id}\n";
}
```

**Para OPCIÓN B:**
```php
// Mantener el más reciente activo, desactivar los demás
$duplicates = Standard::select('part_id')
    ->where('active', true)
    ->groupBy('part_id')
    ->havingRaw('COUNT(*) > 1')
    ->pluck('part_id');

foreach ($duplicates as $partId) {
    $keep = Standard::where('part_id', $partId)
        ->where('active', true)
        ->orderBy('created_at', 'desc')
        ->first();

    Standard::where('part_id', $partId)
        ->where('active', true)
        ->where('id', '!=', $keep->id)
        ->update(['active' => false]); // SOFT: desactivar

    echo "Part {$partId}: Kept #{$keep->id} active\n";
}
```

---

## Troubleshooting

### Error: "Duplicate entry for key 'unique_part_id'"

Esto significa que la limpieza de duplicados no se ejecutó correctamente antes de agregar el constraint.

**Solución:**
```bash
# Revertir migración
php artisan migrate:rollback

# Limpiar duplicados manualmente (ver sección anterior)

# Ejecutar migración de nuevo
php artisan migrate
```

### Error: "Already exists an active standard"

Esto es CORRECTO. La validación está funcionando.

Si necesitas cambiar el estándar:
1. Desactiva el anterior (`active = false`)
2. Crea o activa el nuevo (`active = true`)

### ¿Cómo cambio de OPCIÓN A a OPCIÓN B después?

**NO ES TRIVIAL.** Si implementaste OPCIÓN A y eliminaste columnas (`active`, `effective_date`), necesitarás:

1. Revertir migración de UNIQUE
2. Restaurar columnas eliminadas
3. Implementar OPCIÓN B
4. Perderás el historial eliminado (no recuperable)

**Recomendación:** Elige bien desde el inicio. Si tienes dudas → usa OPCIÓN B (más flexible).

---

## Checklist de Implementación

### OPCIÓN A: UNIQUE Simple

- [ ] Renombrar migración `.example`
- [ ] Revisar lógica de limpieza en migración
- [ ] Ejecutar `php artisan migrate`
- [ ] Verificar con tinker (intentar crear duplicado)
- [ ] Actualizar tests
- [ ] Opcional: Eliminar campos `active`, `effective_date`
- [ ] Opcional: Actualizar modelo y componentes Livewire
- [ ] Opcional: Eliminar scopes y métodos innecesarios

### OPCIÓN B: Validación en App

- [ ] Renombrar migración `.example`
- [ ] Ejecutar `php artisan migrate`
- [ ] Renombrar `UniqueActiveStandard.php.example`
- [ ] Actualizar `StandardCreate.php` con rule
- [ ] Actualizar `StandardEdit.php` con rule
- [ ] Agregar helpers al modelo (opcional)
- [ ] Agregar UI de historial (opcional)
- [ ] Crear tests `StandardUniqueActiveTest`
- [ ] Ejecutar tests
- [ ] Verificación manual con tinker
- [ ] Probar en UI (crear duplicado activo, debe fallar)

---

## Contacto y Soporte

Para preguntas o problemas:
- Revisa el análisis completo: `12_standards_unique_part_validation_analysis.md`
- Revisa el código de las migraciones (contienen comentarios detallados)
- Revisa el código de la rule `UniqueActiveStandard.php`

Autor: Agent Architect
Fecha: 2026-01-13
Versión: 1.0
