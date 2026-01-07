# Datos de Prueba - Llenar Manualmente en el Sistema

## ⚠️ IMPORTANTE: Orden de llenado

1. Primero crear el **Part**
2. Luego crear el **Price** (con fecha efectiva PASADA o de HOY)
3. Finalmente crear el **Purchase Order**

---

## 1. PART (Parte) - Ir a /admin/parts/create

| Campo | Valor |
|-------|-------|
| Number | PN-TEST-001 |
| Item Number | ITEM-TEST-001 |
| Unit of Measure | PCS |
| Active | ✅ Sí |
| Description | Parte de prueba |
| Notes | Test |

---

## 2. PRICE (Precio) - Ir a /admin/prices/create

⚠️ **La fecha efectiva debe ser HOY o una fecha PASADA, no futura**

| Campo | Valor |
|-------|-------|
| Part | PN-TEST-001 (seleccionar del dropdown) |
| Unit Price | 5.0000 |
| Tier 1-999 | 5.0000 |
| Tier 1,000-10,999 | 4.5000 |
| Tier 11,000-99,999 | 4.0000 |
| Tier 100,000+ | 3.5000 |
| Effective Date | **05/01/2026** (hoy o antes) |
| Active | ✅ Sí |
| Comments | Precio de prueba |

---

## 3. PURCHASE ORDER - Ir a /admin/purchase-orders/create

| Campo | Valor |
|-------|-------|
| Número de PO | PO-TEST-001 |
| WO | WO-TEST-001 |
| Parte | PN-TEST-001 (el mismo que creaste) |
| Fecha de PO | 05/01/2026 |
| Fecha de Entrega | 20/01/2026 |
| Cantidad | 5000 |
| Precio Unitario | **4.5000** (tier 1,000-10,999) |
| PDF | Subir cualquier PDF |
| Comentarios | Orden de prueba |

---

## Referencia de Precios por Cantidad

| Si la cantidad es... | Usar este precio |
|---------------------|------------------|
| 1 - 999 | 5.0000 |
| 1,000 - 10,999 | 4.5000 |
| 11,000 - 99,999 | 4.0000 |
| 100,000+ | 3.5000 |

---

## Checklist antes de crear PO

- [ ] El Part existe y está activo
- [ ] El Price existe para ese Part
- [ ] El Price está marcado como activo
- [ ] La fecha efectiva del Price es HOY o ANTES (no futura)
- [ ] El precio unitario en la PO coincide con el tier correcto
