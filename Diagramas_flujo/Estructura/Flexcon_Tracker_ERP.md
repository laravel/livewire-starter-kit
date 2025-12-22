Design Document - FlexCon Tracker ERP
Overview
FlexCon Tracker ERP es un sistema de gestión de producción construido con el stack TALL (Tailwind CSS, Alpine.js, Laravel, Livewire). El sistema gestiona el flujo
completo desde la recepción de Purchase Orders hasta la facturación, dividido en 5 fases de implementación progresiva.
Architecture
Stack Tecnológico
Backend: Laravel 12.x
Frontend: Livewire 3.x (componentes estándar)
CSS: Tailwind CSS 3.x
JavaScript: Alpine.js 3.x
Database: MySQL/PostgreSQL
Authentication: Laravel Breeze + Spatie Permissions
Arquitectura de Capas
┌─────────────────────────────────────────────────────────────┐
│ Presentation Layer │
│ (Blade Views + Livewire Components) │
├─────────────────────────────────────────────────────────────┤
│ Application Layer │
│ (Livewire Components + Controllers + Form Requests) │
├─────────────────────────────────────────────────────────────┤
│ Domain Layer │
│ (Models + Services + Policies + Events) │
├─────────────────────────────────────────────────────────────┤
│ Infrastructure Layer │
│ (Repositories + Database + External Services) │
└─────────────────────────────────────────────────────────────┘
Diagrama de Flujo General del Sistema
flowchart TD
A[Recibir PO] --> B{Validar Precio}
B -->|OK| C[Crear WO]
B -->|Error| D[Solicitar Corrección]
C --> E[Calcular Capacidad]
E --> F[Lista Envío Preliminar]
F --> G[Preparar Kits]
G --> H[Ensamble]
H --> I[Inspección]
I -->|OK| J[Empaque]
I -->|Rechazo| K[Acción Correctiva]
K --> H
J --> L[Shipping List]
L --> M[Invoice]
M --> N{WO Completo?}
N -->|Sí| O[Cerrar WO]
N -->|No| P[BackOrder]
P --> E
Components and Interfaces
Estructura de Directorios Propuestaapp/
├── Http/
│ └── Controllers/
│ ├── PurchaseOrderController.php
│ ├── WorkOrderController.php
│ ├── ProductionCapacityController.php
│ ├── ShippingListController.php
│ └── InvoiceController.php
├── Livewire/
│ └── Admin/
│ ├── PurchaseOrders/
│ ├── WorkOrders/
│ ├── Production/
│ ├── Shipping/
│ ├── Invoices/
│ └── Quality/
├── Models/
│ ├── PurchaseOrder.php
│ ├── WorkOrder.php
│ ├── Price.php
│ ├── Standard.php
│ ├── ProductionCapacity.php
│ ├── SentList.php
│ ├── Lot.php
│ ├── ShippingList.php
│ ├── Invoice.php
│ ├── BackOrder.php
│ ├── Kit.php
│ ├── Inspection.php
│ └── OverTime.php
├── Services/
│ ├── PurchaseOrderService.php
│ ├── CapacityCalculatorService.php
│ ├── InvoiceGeneratorService.php
│ └── QualityControlService.php
└──
Enums/
├── WorkOrderStatus.php
├── InspectionStatus.php
└──
ShippingStatus.php
Interfaces de Servicios Principales
interface PurchaseOrderServiceInterface
{
public function validatePrice(PurchaseOrder $po): bool;
public function createWorkOrder(PurchaseOrder $po): WorkOrder;
public function markAsPendingCorrection(PurchaseOrder $po, string $reason): void;
} i
nterface CapacityCalculatorServiceInterface
{
public function calculateAvailableHours(Shift $shift, Carbon $startDate, Carbon $endDate): float;
public function calculateRequiredHours(Part $part, int $quantity): float;
public function checkCapacity(array $workOrders, Shift $shift): CapacityResult;
} i
nterface InvoiceGeneratorServiceInterface
{
public function createFromShippingList(ShippingList $shippingList): Invoice;
public function calculateLineTotal(InvoiceLine $line): float;
public function addAdditionalCosts(Invoice $invoice, array $costs): void;
}Data Models
Diagrama Entidad-Relación
erDiagram
PURCHASE_ORDER ||--o{ WORK_ORDER : generates
PART ||--o{ PURCHASE_ORDER : contains
PART ||--o{ PRICE : has
PART ||--o{ PRODUCTION_STANDARD : has
WORK_ORDER ||--o{ LOT : contains
WORK_ORDER ||--o{ SENT_LIST : appears_in
WORK_ORDER }o--|| STATUS : has
SHIFT ||--o{ PRODUCTION_CAPACITY : uses
SHIFT ||--o{ OVER_TIME : extends
SENT_LIST ||--o{ SHIPPING_LIST : generates
SHIPPING_LIST ||--o{ INVOICE : generates
WORK_ORDER ||--o{ BACK_ORDER : creates
WORK_ORDER ||--o{ KIT : requires
KIT ||--o{ INSPECTION : undergoes
Modelos Nuevos a Crear
PurchaseOrder
Schema::create('purchase_orders', function (Blueprint $table) {
$table->id();
$table->string('po_number')->unique();
$table->foreignId('part_id')->constrained();
$table->date('po_date');
$table->date('due_date');
$table->integer('quantity');
$table->decimal('unit_price', 10, 4);
$table->string('status')->default('pending'); // pending, approved, rejected
$table->text('comments')->nullable();
$table->string('pdf_path')->nullable();
$table->timestamps();
$table->softDeletes();
});
WorkOrder
Schema::create('work_orders', function (Blueprint $table) {
$table->id();
$table->string('wo_number')->unique();
$table->foreignId('purchase_order_id')->constrained();
$table->foreignId('status_id')->constrained('statuses_wo');
$table->integer('sent_pieces')->default(0);
$table->date('scheduled_send_date')->nullable();
$table->date('actual_send_date')->nullable();
$table->date('opened_date');
$table->string('eq')->nullable(); // Equipment
$table->string('pr')->nullable(); // Personnel
$table->text('comments')->nullable();
$table->timestamps();
$table->softDeletes();
});
PriceSchema::create('prices', function (Blueprint $table) {
$table->id();
$table->foreignId('part_id')->constrained();
$table->decimal('unit_price', 10, 4);
$table->decimal('tier_1_999', 10, 4)->nullable();
$table->decimal('tier_1000_10999', 10, 4)->nullable();
$table->decimal('tier_11000_99999', 10, 4)->nullable();
$table->decimal('tier_100000_plus', 10, 4)->nullable();
$table->date('effective_date');
$table->boolean('active')->default(true);
$table->text('comments')->nullable();
$table->timestamps();
});
Standard
Schema::create('standards', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->integer('units_per_hour');
$table->text('description')->nullable();
$table->text('comments')->nullable();
$table->boolean('active')->default(true);
$table->timestamps();
});
ProductionStandard (Pivot)
Schema::create('production_standards', function (Blueprint $table) {
$table->id();
$table->foreignId('part_id')->constrained();
$table->foreignId('standard_id')->constrained();
$table->integer('personnel_count')->default(1);
$table->text('comments')->nullable();
$table->timestamps();
});
Lot
Schema::create('lots', function (Blueprint $table) {
$table->id();
$table->foreignId('work_order_id')->constrained();
$table->string('lot_number');
$table->text('description')->nullable();
$table->integer('quantity');
$table->string('status')->default('pending');
$table->text('comments')->nullable();
$table->timestamps();
$table->softDeletes();
$table->unique(['work_order_id', 'lot_number']);
});
SentListSchema::create('sent_lists', function (Blueprint $table) {
$table->id();
$table->date('send_date');
$table->foreignId('work_order_id')->constrained();
$table->foreignId('status_id')->constrained('statuses_wo');
$table->integer('send_qty');
$table->integer('pending_qty');
$table->text('comments')->nullable();
$table->timestamps();
});
ShippingList
Schema::create('shipping_lists', function (Blueprint $table) {
$table->id();
$table->string('packing_slip_number')->unique();
$table->date('ship_date');
$table->foreignId('work_order_id')->constrained();
$table->integer('total_boxes');
$table->json('box_types')->nullable();
$table->string('status')->default('pending');
$table->foreignId('packaging_reviewer_id')->nullable()->constrained('users');
$table->foreignId('inspector_id')->nullable()->constrained('users');
$table->foreignId('cm_reviewer_id')->nullable()->constrained('users');
$table->text('notes')->nullable();
$table->timestamps();
$table->softDeletes();
});
Invoice
Schema::create('invoices', function (Blueprint $table) {
$table->id();
$table->string('invoice_number')->unique();
$table->date('invoice_date');
$table->foreignId('shipping_list_id')->constrained();
$table->decimal('subtotal', 12, 2);
$table->decimal('additional_costs', 12, 2)->default(0);
$table->decimal('total', 12, 2);
$table->string('production_type'); // employee, machine
$table->string('status')->default('draft');
$table->text('comments')->nullable();
$table->timestamps();
$table->softDeletes();
});
InvoiceLine
Schema::create('invoice_lines', function (Blueprint $table) {
$table->id();
$table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
$table->foreignId('lot_id')->constrained();
$table->string('description');
$table->string('item_number');
$table->integer('quantity');
$table->decimal('unit_cost', 10, 4);
$table->decimal('line_total', 12, 2);
$table->timestamps();
});
BackOrderSchema::create('back_orders', function (Blueprint $table) {
$table->id();
$table->foreignId('work_order_id')->constrained();
$table->integer('original_quantity');
$table->integer('shipped_quantity');
$table->integer('pending_quantity');
$table->boolean('rescheduled')->default(false);
$table->date('rescheduled_date')->nullable();
$table->string('status')->default('open');
$table->text('log')->nullable();
$table->timestamps();
});
Kit
Schema::create('kits', function (Blueprint $table) {
$table->id();
$table->foreignId('work_order_id')->constrained();
$table->string('kit_number')->unique();
$table->string('status')->default('preparing'); // preparing, ready, released, in_assembly
$table->boolean('validated')->default(false);
$table->text('validation_notes')->nullable();
$table->foreignId('prepared_by')->nullable()->constrained('users');
$table->foreignId('released_by')->nullable()->constrained('users');
$table->timestamps();
});
Inspection
Schema::create('inspections', function (Blueprint $table) {
$table->id();
$table->foreignId('lot_id')->constrained();
$table->foreignId('inspector_id')->constrained('users');
$table->integer('total_pieces');
$table->integer('approved_pieces');
$table->integer('rejected_pieces');
$table->decimal('rejection_rate', 5, 2);
$table->string('status'); // approved, rejected_minor, rejected_major
$table->json('forms_generated')->nullable(); // FCA-07, FCA-10, etc.
$table->boolean('corrective_action_required')->default(false);
$table->text('notes')->nullable();
$table->timestamps();
});
OverTime
Schema::create('over_times', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->time('start_time');
$table->time('end_time');
$table->integer('break_minutes')->default(0);
$table->integer('employees_qty');
$table->date('date');
$table->foreignId('shift_id')->constrained();
$table->text('comments')->nullable();
$table->timestamps();
});
StatusWOSchema::create('statuses_wo', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->string('color')->default('#6B7280');
$table->text('comments')->nullable();
$table->timestamps();
});
Propiedades de Corrección
Una propiedad es una característica o comportamiento que debe cumplirse en todas las ejecuciones válidas de un sistema; esencialmente, una declaración formal
sobre lo que el sistema debe hacer. Las propiedades sirven como puente entre las especificaciones legibles por humanos y las garantías de corrección verificables
por máquina.
Basado en el análisis previo, se han identificado las siguientes propiedades de corrección:
Propiedad 1: La Validación de Precio Determina el Estado de la OC
Para cualquier Orden de Compra con un precio unitario, compararlo contra el precio registrado para esa parte DEBERÁ resultar en un estado "aprobado" (si los
precios coinciden) o estado "pendiente_de_corrección" (si los precios no coinciden). Valida: Requisitos 1.2, 1.3, 1.4
Propiedad 2: La Autorización de la OC Crea una Orden de Trabajo
Para cualquier Orden de Compra autorizada, DEBERÁ existir exactamente una Orden de Traba
jo vinculada a esa OC con el estado "Abierto". Valida: Requisitos 1.5
Propiedad 3: Selección de Nivel de Precio por Cantidad
Para cualquier cantidad de orden, el sistema DEBERÁ seleccionar el nivel de precios correcto: tier_1_999 para cantidades de 1-999, tier_1000_10999 para cantidades
de 1000-10999, tier_11000_99999 para cantidades de 11000-99999, tier_100000_plus para cantidades >= 100000. Valida: Requisitos 2.3
Propiedad 4: Cálculo del Tiempo de Producción
Para cualquier parte con un estándar de producción y cualquier cantidad, las horas requeridas DEBERÁN ser igual a la cantidad dividida por unidades_por_hora.
Valida: Requisitos 3.3, 4.3
Propiedad 5: Cálculo de Horas Disponibles
Para cualquier turno y rango de fechas, las horas disponibles DEBERÁN ser igual al total de horas del turno menos tiempos de descanso, menos días festivos, menos
cualquier descanso por tiempo extra programado, más horas de tiempo extra. Valida: Requisitos 4.2, 4.5, 13.2
Propiedad 6: Prevención de Desbordamiento de Capacidad
Para cualquier programa de producción, si la suma de las horas requeridas excede las horas disponibles, el sistema DEBERÁ rechazar la adición de órdenes de
trabajo adicionales. Valida: Requisitos 4.4
Propiedad 7: Unicidad del Número de Orden de Trabajo
Para cualesquiera dos Órdenes de Traba
jo en el sistema, sus números de OT (WO numbers) DEBERÁN ser diferentes. Valida: Requisitos 5.1
Propiedad 8: La Cantidad del Lote Actualiza la OT
Para cualquier lote completado, las piezas enviadas
(sent_pieces) de la Orden de Trabajo padre DEBERÁN aumentar por la cantidad del lote. Valida: Requisitos 6.2,
7.2
Propiedad 9: Cálculo de Cantidad Pendiente
Para cualquier Orden de Traba
jo, la cantidad pendiente (pending_quantity) DEBERÁ ser igual a la cantidad original menos la suma de todas las cantidades de los lotes
completados. Valida: Requisitos 6.3
Propiedad 10: Números de Lote Secuenciales
Para cualquier Orden de Traba
jo con múltiples lotes, los números de lote DEBERÁN ser secuenciales comenzando desde el 1. Valida: Requisitos 7.3
Propiedad 11: Requisitos de Formulario por Tasa de Rechazo
Para cualquier inspección con una tasa de rechazo entre 5% y 14%, los formularios FCA-10 y FCA-16 DEBERÁN ser requeridos. Para una tasa de rechazo >= 15%, los
formularios FCA-10, FCA-16 y una acción correctiva DEBERÁN ser requeridos. Valida: Requisitos 9.2, 9.3
Propiedad 12: Cálculo del Total de Línea de FacturaPara cualquier línea de factura, el total de línea
(line_total) DEBERÁ ser igual a la cantidad multiplicada por el costo unitario. Valida: Requisitos 11.3
Propiedad 13: Multiplicador de Tipo de Producción
Para cualquier factura, si el tipo de producción es "empleado" el multiplicador DEBERÁ ser 1, si el tipo de producción es "máquina" el multiplicador DEBERÁ ser 20.
Valida: Requisitos 11.4, 11.5
Propiedad 14: Integridad de Cantidad de BackOrder
Para cualquier BackOrder
(Pedido Pendiente), la cantidad enviada más la cantidad pendiente DEBERÁN ser igual a la cantidad original. Valida: Requisitos 12.2, 12.3
Propiedad 15: Unicidad de la Lista de Empaque
Para cualesquiera dos Listas de Envío, su número de lista de empaque
(packing_slip_number) DEBERÁ ser diferente. Valida: Requisitos 10.1
Propiedad 16: Unicidad del Número de Factura
Para cualesquiera dos Facturas, su número de factura
(invoice_number) DEBERÁ ser diferente. Valida: Requisitos 11.1
Error Handling
Estrategia de Manejo de Errores
1. Validation Errors: Usar Form Requests de Laravel para validación de entrada
2. Business Logic Errors: Excepciones personalizadas con mensajes descriptivos
3. Database Errors: Transacciones para operaciones críticas
4. External Service Errors: Retry logic con circuit breaker pattern
Excepciones Personalizadas
namespace App\Exceptions;
class PriceValidationException extends Exception {}
class CapacityExceededException extends Exception {}
class WorkOrderNotFoundException extends Exception {}
class InspectionFailedException extends Exception {}
Testing Strategy
Framework de Testing
Unit Tests: PHPUnit (incluido en Laravel)
Property-Based Tests: PHPQuickCheck o similar
Feature Tests: Laravel HTTP Tests
Browser Tests: Laravel Dusk (opcional)
Dual Testing Approach
Unit Tests
Validación de modelos y relaciones
Cálculos de servicios
Transformaciones de datos
Property-Based Tests
Cada propiedad de correctness será implementada como un test PBT
Mínimo 100 iteraciones por propiedad
Generadores personalizados para modelos del dominio
Estructura de Teststests/
├── Unit/
│ ├── Models/
│ ├── Services/
│ └── Calculations/
├── Feature/
│ ├── PurchaseOrders/
│ ├── WorkOrders/
│ ├── Production/
│ └── Invoices/
└──
Property/
├── PriceValidationPropertyTest.php
├── CapacityCalculationPropertyTest.php
├── InvoiceCalculationPropertyTest.php
└──
QuantityIntegrityPropertyTest.php
Implementación de Fases
FASE 1: Fundamentos de Órdenes (Semanas 1-2)
Objetivo: Establecer el flujo básico PO → WO
Módulos:
Statuses WO (catálogo de estados) - Completado
Prices (precios por parte) - Completado
Purchase Orders (CRUD completo) - Completado
Work Orders (CRUD básico) - Completado
Parts (CRUD Completo) - Completado
Seeds: WorOrderTestSeeder
Creados y cargados para probar los diferentes modulos
FASE 2: Planificación de Producción (Semanas 3-4)
Objetivo: Calcular capacidad y generar listas de envío
Módulos:
Standards (estándares de producción)
Production Standards (relación part-standard)
Over Time (tiempo extra)
Production Capacity (calculadora)
Sent List (lista de envío preliminar)
Dependencias: Shifts, Holidays, Break Times (ya existen)
FASE 3: Producción y Lotes (Semanas 5-6)
Objetivo: Gestionar kits y lotes de producción
Módulos:
Kits (preparación de materiales)
Lots (lotes de producción)
Kit Incidents (registro de incidencias)
Dependencias: Work Orders (Fase 1)
FASE 4: Calidad y Envío (Semanas 7-8)
Objetivo: Control de calidad y documentación de envío
Módulos:
Inspections (inspección de ensamble)
Quality Forms (FCA-07, FCA-10, etc.)Shipping Lists (lista de envío final)
Dependencias: Lots (Fase 3)
FASE 5: Facturación y Cierre (Semanas 9-10)
Objetivo: Generar facturas y gestionar backorders
Módulos:
Invoices (facturas)
Invoice Lines (líneas de factura)
BackOrders (órdenes pendientes)
WO Closure (cierre de órdenes)
Dependencias: Shipping Lists (Fase 4)
Diagrama de Dependencias por Faseflowchart LR
subgraph Existente
A[Parts]
B[Shifts]
C[Holidays]
D[Break Times]
E[Employees]
F[Areas]
end
subgraph Fase1[Fase 1]
G[Statuses WO]
H[Prices]
I[Purchase Orders]
J[Work Orders]
end
subgraph Fase2[Fase 2]
K[Standards]
L[Production Standards]
M[Over Time]
N[Production Capacity]
O[Sent List]
end
subgraph Fase3[Fase 3]
P[Kits]
Q[Lots]
end
subgraph Fase4[Fase 4]
R[Inspections]
S[Shipping Lists]
end
subgraph Fase5[Fase 5]
T[Invoices]
U[BackOrders]
end
A --> H
A --> I
I --> J
G --> J
B --> N
C --> N
D --> N
K --> L
A --> L
J --> O
M --> N
J --> P
J --> Q
Q --> R
Q --> S
S --> T
J --> app/
├── Http/
│ └── Controllers/
│ ├── PurchaseOrderController.php
│ ├── WorkOrderController.php
│ ├── ProductionCapacityController.php
│ ├── ShippingListController.php
│ └── InvoiceController.php
├── Livewire/
│ └── Admin/
│ ├── PurchaseOrders/
│ ├── WorkOrders/
│ ├── Production/
│ ├── Shipping/
│ ├── Invoices/
│ └── Quality/
├── Models/
│ ├── PurchaseOrder.php
│ ├── WorkOrder.php
│ ├── Price.php
│ ├── Standard.php
│ ├── ProductionCapacity.php
│ ├── SentList.php
│ ├── Lot.php
│ ├── ShippingList.php
│ ├── Invoice.php
│ ├── BackOrder.php
│ ├── Kit.php
│ ├── Inspection.php
│ └── OverTime.php
├── Services/
│ ├── PurchaseOrderService.php
│ ├── CapacityCalculatorService.php
│ ├── InvoiceGeneratorService.php
│ └── QualityControlService.php
└──
Enums/
├── WorkOrderStatus.php
├── InspectionStatus.php
└──
ShippingStatus.php
Interfaces de Servicios Principales
interface PurchaseOrderServiceInterface
{
public function validatePrice(PurchaseOrder $po): bool;
public function createWorkOrder(PurchaseOrder $po): WorkOrder;
public function markAsPendingCorrection(PurchaseOrder $po, string $reason): void;
} i
nterface CapacityCalculatorServiceInterface
{
public function calculateAvailableHours(Shift $shift, Carbon $startDate, Carbon $endDate): float;
public function calculateRequiredHours(Part $part, int $quantity): float;
public function checkCapacity(array $workOrders, Shift $shift): CapacityResult;
} i
nterface InvoiceGeneratorServiceInterface
{
public function createFromShippingList(ShippingList $shippingList): Invoice;
public function calculateLineTotal(InvoiceLine $line): float;
public function addAdditionalCosts(Invoice $invoice, array $costs): void;
}Data Models
Diagrama Entidad-Relación
erDiagram
PURCHASE_ORDER ||--o{ WORK_ORDER : generates
PART ||--o{ PURCHASE_ORDER : contains
PART ||--o{ PRICE : has
PART ||--o{ PRODUCTION_STANDARD : has
WORK_ORDER ||--o{ LOT : contains
WORK_ORDER ||--o{ SENT_LIST : appears_in
WORK_ORDER }o--|| STATUS : has
SHIFT ||--o{ PRODUCTION_CAPACITY : uses
SHIFT ||--o{ OVER_TIME : extends
SENT_LIST ||--o{ SHIPPING_LIST : generates
SHIPPING_LIST ||--o{ INVOICE : generates
WORK_ORDER ||--o{ BACK_ORDER : creates
WORK_ORDER ||--o{ KIT : requires
KIT ||--o{ INSPECTION : undergoes
Modelos Nuevos a Crear
PurchaseOrder
Schema::create('purchase_orders', function (Blueprint $table) {
$table->id();
$table->string('po_number')->unique();
$table->foreignId('part_id')->constrained();
$table->date('po_date');
$table->date('due_date');
$table->integer('quantity');
$table->decimal('unit_price', 10, 4);
$table->string('status')->default('pending'); // pending, approved, rejected
$table->text('comments')->nullable();
$table->string('pdf_path')->nullable();
$table->timestamps();
$table->softDeletes();
});
WorkOrder
Schema::create('work_orders', function (Blueprint $table) {
$table->id();
$table->string('wo_number')->unique();
$table->foreignId('purchase_order_id')->constrained();
$table->foreignId('status_id')->constrained('statuses_wo');
$table->integer('sent_pieces')->default(0);
$table->date('scheduled_send_date')->nullable();
$table->date('actual_send_date')->nullable();
$table->date('opened_date');
$table->string('eq')->nullable(); // Equipment
$table->string('pr')->nullable(); // Personnel
$table->text('comments')->nullable();
$table->timestamps();
$table->softDeletes();
});
PriceSchema::create('prices', function (Blueprint $table) {
$table->id();
$table->foreignId('part_id')->constrained();
$table->decimal('unit_price', 10, 4);
$table->decimal('tier_1_999', 10, 4)->nullable();
$table->decimal('tier_1000_10999', 10, 4)->nullable();
$table->decimal('tier_11000_99999', 10, 4)->nullable();
$table->decimal('tier_100000_plus', 10, 4)->nullable();
$table->date('effective_date');
$table->boolean('active')->default(true);
$table->text('comments')->nullable();
$table->timestamps();
});
Standard
Schema::create('standards', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->integer('units_per_hour');
$table->text('description')->nullable();
$table->text('comments')->nullable();
$table->boolean('active')->default(true);
$table->timestamps();
});
ProductionStandard (Pivot)
Schema::create('production_standards', function (Blueprint $table) {
$table->id();
$table->foreignId('part_id')->constrained();
$table->foreignId('standard_id')->constrained();
$table->integer('personnel_count')->default(1);
$table->text('comments')->nullable();
$table->timestamps();
});
Lot
Schema::create('lots', function (Blueprint $table) {
$table->id();
$table->foreignId('work_order_id')->constrained();
$table->string('lot_number');
$table->text('description')->nullable();
$table->integer('quantity');
$table->string('status')->default('pending');
$table->text('comments')->nullable();
$table->timestamps();
$table->softDeletes();
$table->unique(['work_order_id', 'lot_number']);
});
SentListSchema::create('sent_lists', function (Blueprint $table) {
$table->id();
$table->date('send_date');
$table->foreignId('work_order_id')->constrained();
$table->foreignId('status_id')->constrained('statuses_wo');
$table->integer('send_qty');
$table->integer('pending_qty');
$table->text('comments')->nullable();
$table->timestamps();
});
ShippingList
Schema::create('shipping_lists', function (Blueprint $table) {
$table->id();
$table->string('packing_slip_number')->unique();
$table->date('ship_date');
$table->foreignId('work_order_id')->constrained();
$table->integer('total_boxes');
$table->json('box_types')->nullable();
$table->string('status')->default('pending');
$table->foreignId('packaging_reviewer_id')->nullable()->constrained('users');
$table->foreignId('inspector_id')->nullable()->constrained('users');
$table->foreignId('cm_reviewer_id')->nullable()->constrained('users');
$table->text('notes')->nullable();
$table->timestamps();
$table->softDeletes();
});
Invoice
Schema::create('invoices', function (Blueprint $table) {
$table->id();
$table->string('invoice_number')->unique();
$table->date('invoice_date');
$table->foreignId('shipping_list_id')->constrained();
$table->decimal('subtotal', 12, 2);
$table->decimal('additional_costs', 12, 2)->default(0);
$table->decimal('total', 12, 2);
$table->string('production_type'); // employee, machine
$table->string('status')->default('draft');
$table->text('comments')->nullable();
$table->timestamps();
$table->softDeletes();
});
InvoiceLine
Schema::create('invoice_lines', function (Blueprint $table) {
$table->id();
$table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
$table->foreignId('lot_id')->constrained();
$table->string('description');
$table->string('item_number');
$table->integer('quantity');
$table->decimal('unit_cost', 10, 4);
$table->decimal('line_total', 12, 2);
$table->timestamps();
});
BackOrderSchema::create('back_orders', function (Blueprint $table) {
$table->id();
$table->foreignId('work_order_id')->constrained();
$table->integer('original_quantity');
$table->integer('shipped_quantity');
$table->integer('pending_quantity');
$table->boolean('rescheduled')->default(false);
$table->date('rescheduled_date')->nullable();
$table->string('status')->default('open');
$table->text('log')->nullable();
$table->timestamps();
});
Kit
Schema::create('kits', function (Blueprint $table) {
$table->id();
$table->foreignId('work_order_id')->constrained();
$table->string('kit_number')->unique();
$table->string('status')->default('preparing'); // preparing, ready, released, in_assembly
$table->boolean('validated')->default(false);
$table->text('validation_notes')->nullable();
$table->foreignId('prepared_by')->nullable()->constrained('users');
$table->foreignId('released_by')->nullable()->constrained('users');
$table->timestamps();
});
Inspection
Schema::create('inspections', function (Blueprint $table) {
$table->id();
$table->foreignId('lot_id')->constrained();
$table->foreignId('inspector_id')->constrained('users');
$table->integer('total_pieces');
$table->integer('approved_pieces');
$table->integer('rejected_pieces');
$table->decimal('rejection_rate', 5, 2);
$table->string('status'); // approved, rejected_minor, rejected_major
$table->json('forms_generated')->nullable(); // FCA-07, FCA-10, etc.
$table->boolean('corrective_action_required')->default(false);
$table->text('notes')->nullable();
$table->timestamps();
});
OverTime
Schema::create('over_times', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->time('start_time');
$table->time('end_time');
$table->integer('break_minutes')->default(0);
$table->integer('employees_qty');
$table->date('date');
$table->foreignId('shift_id')->constrained();
$table->text('comments')->nullable();
$table->timestamps();
});
StatusWOSchema::create('statuses_wo', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->string('color')->default('#6B7280');
$table->text('comments')->nullable();
$table->timestamps();
});
Propiedades de Corrección
Una propiedad es una característica o comportamiento que debe cumplirse en todas las ejecuciones válidas de un sistema; esencialmente, una declaración formal
sobre lo que el sistema debe hacer. Las propiedades sirven como puente entre las especificaciones legibles por humanos y las garantías de corrección verificables
por máquina.
Basado en el análisis previo, se han identificado las siguientes propiedades de corrección:
Propiedad 1: La Validación de Precio Determina el Estado de la OC
Para cualquier Orden de Compra con un precio unitario, compararlo contra el precio registrado para esa parte DEBERÁ resultar en un estado "aprobado" (si los
precios coinciden) o estado "pendiente_de_corrección" (si los precios no coinciden). Valida: Requisitos 1.2, 1.3, 1.4
Propiedad 2: La Autorización de la OC Crea una Orden de Trabajo
Para cualquier Orden de Compra autorizada, DEBERÁ existir exactamente una Orden de Traba
jo vinculada a esa OC con el estado "Abierto". Valida: Requisitos 1.5
Propiedad 3: Selección de Nivel de Precio por Cantidad
Para cualquier cantidad de orden, el sistema DEBERÁ seleccionar el nivel de precios correcto: tier_1_999 para cantidades de 1-999, tier_1000_10999 para cantidades
de 1000-10999, tier_11000_99999 para cantidades de 11000-99999, tier_100000_plus para cantidades >= 100000. Valida: Requisitos 2.3
Propiedad 4: Cálculo del Tiempo de Producción
Para cualquier parte con un estándar de producción y cualquier cantidad, las horas requeridas DEBERÁN ser igual a la cantidad dividida por unidades_por_hora.
Valida: Requisitos 3.3, 4.3
Propiedad 5: Cálculo de Horas Disponibles
Para cualquier turno y rango de fechas, las horas disponibles DEBERÁN ser igual al total de horas del turno menos tiempos de descanso, menos días festivos, menos
cualquier descanso por tiempo extra programado, más horas de tiempo extra. Valida: Requisitos 4.2, 4.5, 13.2
Propiedad 6: Prevención de Desbordamiento de Capacidad
Para cualquier programa de producción, si la suma de las horas requeridas excede las horas disponibles, el sistema DEBERÁ rechazar la adición de órdenes de
trabajo adicionales. Valida: Requisitos 4.4
Propiedad 7: Unicidad del Número de Orden de Trabajo
Para cualesquiera dos Órdenes de Traba
jo en el sistema, sus números de OT (WO numbers) DEBERÁN ser diferentes. Valida: Requisitos 5.1
Propiedad 8: La Cantidad del Lote Actualiza la OT
Para cualquier lote completado, las piezas enviadas
(sent_pieces) de la Orden de Trabajo padre DEBERÁN aumentar por la cantidad del lote. Valida: Requisitos 6.2,
7.2
Propiedad 9: Cálculo de Cantidad Pendiente
Para cualquier Orden de Traba
jo, la cantidad pendiente (pending_quantity) DEBERÁ ser igual a la cantidad original menos la suma de todas las cantidades de los lotes
completados. Valida: Requisitos 6.3
Propiedad 10: Números de Lote Secuenciales
Para cualquier Orden de Traba
jo con múltiples lotes, los números de lote DEBERÁN ser secuenciales comenzando desde el 1. Valida: Requisitos 7.3
Propiedad 11: Requisitos de Formulario por Tasa de Rechazo
Para cualquier inspección con una tasa de rechazo entre 5% y 14%, los formularios FCA-10 y FCA-16 DEBERÁN ser requeridos. Para una tasa de rechazo >= 15%, los
formularios FCA-10, FCA-16 y una acción correctiva DEBERÁN ser requeridos. Valida: Requisitos 9.2, 9.3
Propiedad 12: Cálculo del Total de Línea de FacturaPara cualquier línea de factura, el total de línea
(line_total) DEBERÁ ser igual a la cantidad multiplicada por el costo unitario. Valida: Requisitos 11.3
Propiedad 13: Multiplicador de Tipo de Producción
Para cualquier factura, si el tipo de producción es "empleado" el multiplicador DEBERÁ ser 1, si el tipo de producción es "máquina" el multiplicador DEBERÁ ser 20.
Valida: Requisitos 11.4, 11.5
Propiedad 14: Integridad de Cantidad de BackOrder
Para cualquier BackOrder
(Pedido Pendiente), la cantidad enviada más la cantidad pendiente DEBERÁN ser igual a la cantidad original. Valida: Requisitos 12.2, 12.3
Propiedad 15: Unicidad de la Lista de Empaque
Para cualesquiera dos Listas de Envío, su número de lista de empaque
(packing_slip_number) DEBERÁ ser diferente. Valida: Requisitos 10.1
Propiedad 16: Unicidad del Número de Factura
Para cualesquiera dos Facturas, su número de factura
(invoice_number) DEBERÁ ser diferente. Valida: Requisitos 11.1
Error Handling
Estrategia de Manejo de Errores
1. Validation Errors: Usar Form Requests de Laravel para validación de entrada
2. Business Logic Errors: Excepciones personalizadas con mensajes descriptivos
3. Database Errors: Transacciones para operaciones críticas
4. External Service Errors: Retry logic con circuit breaker pattern
Excepciones Personalizadas
namespace App\Exceptions;
class PriceValidationException extends Exception {}
class CapacityExceededException extends Exception {}
class WorkOrderNotFoundException extends Exception {}
class InspectionFailedException extends Exception {}
Testing Strategy
Framework de Testing
Unit Tests: PHPUnit (incluido en Laravel)
Property-Based Tests: PHPQuickCheck o similar
Feature Tests: Laravel HTTP Tests
Browser Tests: Laravel Dusk (opcional)
Dual Testing Approach
Unit Tests
Validación de modelos y relaciones
Cálculos de servicios
Transformaciones de datos
Property-Based Tests
Cada propiedad de correctness será implementada como un test PBT
Mínimo 100 iteraciones por propiedad
Generadores personalizados para modelos del dominio
Estructura de Teststests/
├── Unit/
│ ├── Models/
│ ├── Services/
│ └── Calculations/
├── Feature/
│ ├── PurchaseOrders/
│ ├── WorkOrders/
│ ├── Production/
│ └── Invoices/
└──
Property/
├── PriceValidationPropertyTest.php
├── CapacityCalculationPropertyTest.php
├── InvoiceCalculationPropertyTest.php
└──
QuantityIntegrityPropertyTest.php
Implementación de Fases
FASE 1: Fundamentos de Órdenes (Semanas 1-2)
Objetivo: Establecer el flujo básico PO → WO
Módulos:
Statuses WO (catálogo de estados) - Completado
Prices (precios por parte) - Completado
Purchase Orders (CRUD completo) - Completado
Work Orders (CRUD básico) - Completado
Parts (CRUD Completo) - Completado
Seeds: WorOrderTestSeeder
Creados y cargados para probar los diferentes modulos
FASE 2: Planificación de Producción (Semanas 3-4)
Objetivo: Calcular capacidad y generar listas de envío
Módulos:
Standards (estándares de producción)
Production Standards (relación part-standard)
Over Time (tiempo extra)
Production Capacity (calculadora)
Sent List (lista de envío preliminar)
Dependencias: Shifts, Holidays, Break Times (ya existen)
FASE 3: Producción y Lotes (Semanas 5-6)
Objetivo: Gestionar kits y lotes de producción
Módulos:
Kits (preparación de materiales)
Lots (lotes de producción)
Kit Incidents (registro de incidencias)
Dependencias: Work Orders (Fase 1)
FASE 4: Calidad y Envío (Semanas 7-8)
Objetivo: Control de calidad y documentación de envío
Módulos:
Inspections (inspección de ensamble)
Quality Forms (FCA-07, FCA-10, etc.)Shipping Lists (lista de envío final)
Dependencias: Lots (Fase 3)
FASE 5: Facturación y Cierre (Semanas 9-10)
Objetivo: Generar facturas y gestionar backorders
Módulos:
Invoices (facturas)
Invoice Lines (líneas de factura)
BackOrders (órdenes pendientes)
WO Closure (cierre de órdenes)
Dependencias: Shipping Lists (Fase 4)
Diagrama de Dependencias por Faseflowchart LR
subgraph Existente
A[Parts]
B[Shifts]
C[Holidays]
D[Break Times]
E[Employees]
F[Areas]
end
subgraph Fase1[Fase 1]
G[Statuses WO]
H[Prices]
I[Purchase Orders]
J[Work Orders]
end
subgraph Fase2[Fase 2]
K[Standards]
L[Production Standards]
M[Over Time]
N[Production Capacity]
O[Sent List]
end
subgraph Fase3[Fase 3]
P[Kits]
Q[Lots]
end
subgraph Fase4[Fase 4]
R[Inspections]
S[Shipping Lists]
end
subgraph Fase5[Fase 5]
T[Invoices]
U[BackOrders]
end
A --> H
A --> I
I --> J
G --> J
B --> N
C --> N
D --> N
K --> L
A --> L
J --> O
M --> N
J --> P
J --> Q
Q --> R
Q --> S
S --> T
J --> U
