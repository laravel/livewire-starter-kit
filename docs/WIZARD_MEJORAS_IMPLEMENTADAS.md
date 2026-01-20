# Mejoras Implementadas en Capacity Wizard y Listas Preliminares

## Fecha: 20 de Enero, 2026

## Resumen de Cambios

Se han implementado mejoras significativas en el sistema de Capacity Wizard (Step 2 y Step 3) para soportar múltiples Purchase Orders y un flujo de aprobación por departamentos.

---

## 1. Cambios en Base de Datos

### 1.1 Nueva Tabla Pivot: `sent_list_purchase_orders`

Se creó una tabla pivot para relacionar múltiples Purchase Orders con una Sent List:

**Campos:**
- `sent_list_id` - FK a sent_lists
- `purchase_order_id` - FK a purchase_orders
- `quantity` - Cantidad del PO en esta lista
- `required_hours` - Horas requeridas calculadas
- `lot_number` - Número de lote/viajero (opcional)

**Migración:** `2026_01_20_061024_create_sent_list_purchase_orders_table.php`

### 1.2 Campos Nuevos en `sent_lists`

Se agregaron campos para el flujo de departamentos:

**Workflow:**
- `current_department` - Departamento actual (materiales, produccion, calidad, envios)
- `department_history` - JSON con historial de transiciones

**Aprobaciones por Departamento:**
- `materials_approved_at` / `materials_approved_by`
- `production_approved_at` / `production_approved_by`
- `quality_approved_at` / `quality_approved_by`
- `shipping_approved_at` / `shipping_approved_by`

**Otros:**
- `notes` - Notas generales de la lista

**Migración:** `2026_01_20_061033_add_department_fields_to_sent_lists_table.php`

---

## 2. Cambios en Modelos

### 2.1 Modelo `SentList`

**Nuevas Constantes:**
```php
const DEPT_MATERIALS = 'materiales';
const DEPT_PRODUCTION = 'produccion';
const DEPT_QUALITY = 'calidad';
const DEPT_SHIPPING = 'envios';
```

**Nuevas Relaciones:**
- `purchaseOrders()` - BelongsToMany con Purchase Orders
- `materialsApprover()`, `productionApprover()`, etc. - BelongsTo User

**Nuevos Métodos:**
- `getDepartments()` - Lista de departamentos disponibles
- `getDepartmentLabelAttribute()` - Etiqueta del departamento actual
- `moveToNextDepartment($userId)` - Mover al siguiente departamento
- `canDepartmentEdit($department)` - Verificar si un departamento puede editar
- `getCapacityUtilizationAttribute()` - Porcentaje de utilización

### 2.2 Modelo `PurchaseOrder`

**Nueva Relación:**
- `sentLists()` - BelongsToMany con Sent Lists

---

## 3. Cambios en Capacity Wizard (Step 2)

### 3.1 Modal de Purchase Orders

**Filtro Mejorado:**
El modal ahora filtra POs que:
- Tienen status `approved`
- Tienen Work Order asociada con status "Open"
- Tienen configuraciones de estándar activas

**Funcionalidad:**
- Permite seleccionar múltiples POs
- Para cada PO, se puede elegir la configuración de estándar a usar
- Valida que la configuración sea compatible con el personal disponible
- Calcula automáticamente las horas requeridas

**Código Relevante:**
```php
public function getAvailablePOsProperty()
{
    return PurchaseOrder::with(['part.standards.configurations', 'workOrder'])
        ->where('status', PurchaseOrder::STATUS_APPROVED)
        ->whereHas('workOrder.status', function($q) {
            $q->where('name', 'Open');
        })
        ->get();
}
```

---

## 4. Cambios en Step 3: Lista Preliminar

### 4.1 Cambios de Nomenclatura

- ❌ **Antes:** "Cierre"
- ✅ **Ahora:** "Lista Preliminar"

### 4.2 Funcionalidad de Lotes/Viajeros

**Nueva Característica:**
- En el Step 3, se puede asignar un número de lote/viajero a cada PO
- Este dato se guarda en la tabla pivot `sent_list_purchase_orders`
- Es opcional y puede agregarse después en el flujo de departamentos

**Tabla Mejorada:**
```
| # | PO Number | Número de Parte | Descripción | Cantidad | Horas | Lote/Viajero |
|---|-----------|-----------------|-------------|----------|-------|--------------|
| 1 | PO-001    | PART-123        | Desc...     | 500      | 25.5  | [input]      |
```

### 4.3 Generación de Lista

Al generar la lista:
1. Se crea un registro en `sent_lists` con `current_department = 'materiales'`
2. Se asocian todos los POs seleccionados con sus cantidades y horas
3. Se guardan los números de lote si fueron proporcionados
4. El status inicial es `pending`

**Código:**
```php
$sentList = SentList::create([
    'current_department' => SentList::DEPT_MATERIALS,
    'status' => SentList::STATUS_PENDING,
    // ... otros campos
]);

// Asociar POs
foreach ($this->workOrderItems as $index => $item) {
    $sentList->purchaseOrders()->attach($item['po_id'], [
        'quantity' => $item['quantity'],
        'required_hours' => $item['required_hours'],
        'lot_number' => $this->lotNumbers[$index] ?? null,
    ]);
}
```

---

## 5. Flujo de Departamentos

### 5.1 Workflow Implementado

**Secuencia:**
```
Materiales → Producción → Calidad → Envíos → Confirmada
```

### 5.2 Componente Livewire: `SentListDepartmentView`

**Ubicación:** `app/Livewire/Admin/SentLists/SentListDepartmentView.php`

**Funcionalidades:**

1. **Vista de Progreso:**
   - Muestra el workflow visual con los 4 departamentos
   - Indica departamentos completados (verde), actual (azul), y pendientes (gris)
   - Muestra fechas de aprobación

2. **Edición por Departamento:**
   - Solo el departamento actual puede editar la lista
   - Puede modificar:
     - Cantidades de cada PO
     - Números de lote/viajero
     - Notas generales

3. **Aprobación y Movimiento:**
   - Botón "Aprobar y Enviar al Siguiente Departamento"
   - Permite agregar notas de aprobación
   - Guarda timestamp y usuario que aprobó
   - Mueve automáticamente al siguiente departamento
   - Al llegar a "Envíos" y aprobar, la lista se marca como `confirmed`

### 5.3 Permisos

Los permisos se determinan por el rol del usuario:
- `materials` o `admin` → Departamento de Materiales
- `production` → Departamento de Producción
- `quality` → Departamento de Calidad
- `shipping` → Departamento de Envíos

---

## 6. Vistas Actualizadas

### 6.1 Vista Index (`sent-lists/index.blade.php`)

**Mejoras:**
- Muestra cantidad de POs por lista (badge "X PO(s)")
- Muestra primeras 2 partes + contador si hay más
- Columna nueva: "Departamento" (muestra departamento actual)
- Título actualizado a "Listas Preliminares"

### 6.2 Vista Show (`sent-lists/show.blade.php`)

**Nueva Estructura:**
- Card de estado con departamento actual
- Información del período (Semana X - YYYY)
- Resumen de recursos asignados
- Resumen de capacidad con barra de progreso
- **Componente Livewire integrado** para vista de departamento

---

## 7. Resumen de Archivos Modificados/Creados

### Migraciones
- ✅ `2026_01_20_061024_create_sent_list_purchase_orders_table.php`
- ✅ `2026_01_20_061033_add_department_fields_to_sent_lists_table.php`

### Modelos
- ✅ `app/Models/SentList.php`
- ✅ `app/Models/PurchaseOrder.php`

### Livewire
- ✅ `app/Livewire/Admin/CapacityWizard.php`
- ✅ `app/Livewire/Admin/SentLists/SentListDepartmentView.php` (NUEVO)

### Vistas
- ✅ `resources/views/livewire/admin/capacity-wizard/step3.blade.php`
- ✅ `resources/views/livewire/admin/sent-lists/sent-list-department-view.blade.php` (NUEVO)
- ✅ `resources/views/sent-lists/index.blade.php`
- ✅ `resources/views/sent-lists/show.blade.php`

### Controladores
- ✅ `app/Http/Controllers/SentListController.php`

---

## 8. Flujo de Uso Completo

### Paso 1: Crear Lista desde Wizard

1. Usuario va al Capacity Wizard
2. **Step 1:** Selecciona turnos y empleados
3. **Step 2:** 
   - Click en "Cargar desde POs"
   - Se abre modal con POs "Open"
   - Selecciona múltiples POs
   - Elige configuración para cada uno (opcional)
   - Click "Agregar Seleccionados"
4. **Step 3 - Lista Preliminar:**
   - Revisa los POs agregados
   - Opcionalmente asigna números de lote/viajero
   - Click "Generar Lista Preliminar y Enviar a Materiales"

### Paso 2: Flujo por Departamentos

#### Materiales
1. Ve la lista en el index con status "Materiales"
2. Entra a ver detalle
3. Puede editar cantidades y lotes
4. Agrega notas si necesita
5. Click "Aprobar y Enviar al Siguiente Departamento"
6. → Lista pasa a **Producción**

#### Producción
1. Ve la lista con status "Producción"
2. Revisa y puede editar
3. Aprueba
4. → Lista pasa a **Calidad**

#### Calidad
1. Ve la lista con status "Calidad"
2. Revisa y puede editar
3. Aprueba
4. → Lista pasa a **Envíos**

#### Envíos
1. Ve la lista con status "Envíos"
2. Revisa (última revisión)
3. Aprueba
4. → Lista se marca como **CONFIRMED** (completada)

---

## 9. Características Técnicas

### 9.1 Transacciones

Todas las operaciones críticas usan transacciones DB:
```php
DB::transaction(function () {
    // Crear sent list
    // Asociar POs
    // Sync shifts
});
```

### 9.2 Validaciones

- ✅ Solo POs con WO "Open" se muestran en el modal
- ✅ Configuraciones validadas contra personal disponible
- ✅ Solo el departamento actual puede editar
- ✅ No se puede editar lista confirmada o cancelada

### 9.3 Historial

Todas las transiciones entre departamentos se guardan en `department_history`:
```json
[
  {
    "from": "materiales",
    "to": "produccion",
    "user_id": 5,
    "timestamp": "2026-01-20T15:30:00.000000Z"
  }
]
```

---

## 10. Próximos Pasos Sugeridos

### Corto Plazo
- [ ] Agregar notificaciones por email cuando una lista llega a un departamento
- [ ] Dashboard con estadísticas de listas por departamento
- [ ] Exportar PDF de la lista preliminar

### Mediano Plazo
- [ ] Permitir rechazar y devolver al departamento anterior
- [ ] Comentarios por PO individual
- [ ] Integración con sistema de Work Orders para auto-crear WOs

### Largo Plazo
- [ ] App móvil para aprobaciones
- [ ] Firma digital por departamento
- [ ] Analytics de tiempos de aprobación por departamento

---

## 11. Testing

### Para Probar la Funcionalidad

1. **Crear datos de prueba:**
   ```bash
   php artisan db:seed --class=PurchaseOrderSeeder
   php artisan db:seed --class=WorkOrderSeeder
   ```

2. **Crear Purchase Orders con WO Open:**
   - Ir a Purchase Orders
   - Crear PO y aprobar
   - Verificar que se creó WO con status "Open"

3. **Usar Capacity Wizard:**
   - Ir a `/admin/capacity-calculator`
   - Completar Step 1 y 2
   - Abrir modal de POs
   - Seleccionar múltiples POs
   - Generar lista

4. **Probar Flujo de Departamentos:**
   - Cambiar rol de usuario
   - Ver lista preliminar
   - Editar y aprobar
   - Verificar que pasa al siguiente departamento

---

## Conclusión

Se ha implementado exitosamente un sistema completo de:
- ✅ Selección de múltiples POs en el Wizard
- ✅ Configuración flexible de estándares por PO
- ✅ Asignación de lotes/viajeros
- ✅ Flujo de aprobación por 4 departamentos
- ✅ Historial de aprobaciones
- ✅ Interfaz visual del workflow
- ✅ Permisos por departamento

El sistema está listo para ser usado en producción.
