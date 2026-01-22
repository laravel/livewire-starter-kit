<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get part ID from command line argument or use default
$partId = isset($argv[1]) ? (int)$argv[1] : null;

if (!$partId) {
    echo "=== DIAGNÓSTICO DE PRECIOS - TODAS LAS PARTES ===" . PHP_EOL . PHP_EOL;
    
    $parts = App\Models\Part::active()->get();
    
    echo "Total de partes activas: {$parts->count()}" . PHP_EOL . PHP_EOL;
    
    foreach ($parts as $part) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;
        echo "Parte ID: {$part->id} | {$part->number}" . PHP_EOL;
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;
        
        // Check Standards
        $standard = $part->standards()->active()->first();
        if (!$standard) {
            echo "❌ SIN STANDARD ACTIVO" . PHP_EOL;
        } else {
            $assemblyMode = $standard->getAssemblyMode();
            echo "✅ Standard: ID {$standard->id}" . PHP_EOL;
            echo "   Assembly Mode: " . ($assemblyMode ?? '❌ NULL') . PHP_EOL;
            
            if ($assemblyMode) {
                // Map to workstation type
                $map = [
                    'manual' => 'table',
                    'semi_automatic' => 'semi_automatic',
                    'machine' => 'machine',
                ];
                $expectedType = $map[$assemblyMode] ?? null;
                echo "   Tipo esperado: {$expectedType}" . PHP_EOL;
                
                // Check if price exists for this type
                $price = $part->activePriceForWorkstationType($expectedType);
                if ($price) {
                    echo "   ✅ Precio encontrado: \${$price->sample_price} (Tipo: {$price->workstation_type})" . PHP_EOL;
                } else {
                    echo "   ❌ NO HAY PRECIO PARA TIPO: {$expectedType}" . PHP_EOL;
                }
            }
        }
        
        // Show all active prices
        $prices = $part->prices()->active()->get();
        if ($prices->isEmpty()) {
            echo "❌ SIN PRECIOS ACTIVOS" . PHP_EOL;
        } else {
            echo "💰 Precios activos ({$prices->count()}):" . PHP_EOL;
            foreach ($prices as $price) {
                echo "   - Tipo: {$price->workstation_type} | \${$price->sample_price}" . PHP_EOL;
            }
        }
        
        // Test detection
        $service = new App\Services\POPriceDetectionService();
        $result = $service->detectPriceForPart($part->id, 1000);
        
        if ($result->found) {
            echo "✅ DETECCIÓN: OK" . PHP_EOL;
        } else {
            echo "❌ DETECCIÓN FALLÓ: {$result->error}" . PHP_EOL;
        }
        
        echo PHP_EOL;
    }
    
    echo "=== FIN DIAGNÓSTICO ===" . PHP_EOL;
    exit(0);
}

// Single part debug
echo "=== DEBUG PRICE DETECTION - PARTE ID: {$partId} ===" . PHP_EOL . PHP_EOL;

$part = App\Models\Part::find($partId);

if (!$part) {
    echo "❌ Parte no encontrada" . PHP_EOL;
    exit(1);
}

echo "✅ Parte: {$part->number}" . PHP_EOL;
echo "   Descripción: {$part->description}" . PHP_EOL . PHP_EOL;

// Check Standards
$standards = $part->standards()->active()->get();
echo "📊 Standards activos: {$standards->count()}" . PHP_EOL;

if ($standards->isEmpty()) {
    echo "   ❌ No hay standards activos para esta parte" . PHP_EOL;
} else {
    $standard = $standards->first();
    echo "   Standard ID: {$standard->id}" . PHP_EOL;
    echo "   Assembly Mode: " . ($standard->getAssemblyMode() ?? 'NULL') . PHP_EOL;
    echo "   work_table_id: " . ($standard->work_table_id ?? 'NULL') . PHP_EOL;
    echo "   semi_auto_work_table_id: " . ($standard->semi_auto_work_table_id ?? 'NULL') . PHP_EOL;
    echo "   machine_id: " . ($standard->machine_id ?? 'NULL') . PHP_EOL;
}

echo PHP_EOL;

// Check Prices
$prices = $part->prices()->active()->get();
echo "💰 Precios activos: {$prices->count()}" . PHP_EOL;

if ($prices->isEmpty()) {
    echo "   ❌ No hay precios activos para esta parte" . PHP_EOL;
} else {
    foreach ($prices as $price) {
        echo "   - ID: {$price->id}" . PHP_EOL;
        echo "     Tipo: {$price->workstation_type}" . PHP_EOL;
        echo "     Sample Price: \${$price->sample_price}" . PHP_EOL;
        echo "     Fecha efectiva: {$price->effective_date}" . PHP_EOL;
        echo "     Activo: " . ($price->active ? 'Sí' : 'No') . PHP_EOL . PHP_EOL;
    }
}

echo PHP_EOL . "=== PROBANDO DETECCIÓN DE PRECIO ===" . PHP_EOL . PHP_EOL;

// Test price detection
$service = new App\Services\POPriceDetectionService();
$result = $service->detectPriceForPart($part->id, 1000);

echo "Resultado:" . PHP_EOL;
echo "  Found: " . ($result->found ? 'Sí' : 'No') . PHP_EOL;
echo "  Workstation Type: {$result->workstationType}" . PHP_EOL;
echo "  Error: " . ($result->error ?? 'Ninguno') . PHP_EOL;

if ($result->found && $result->price) {
    echo "  Precio encontrado: \${$result->price->sample_price}" . PHP_EOL;
    echo "  Precio para cantidad 1000: \$" . $result->price->getPriceForQuantity(1000) . PHP_EOL;
}

echo PHP_EOL . "=== FIN DEBUG ===" . PHP_EOL;
