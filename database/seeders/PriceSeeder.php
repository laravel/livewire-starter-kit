<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Price;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PriceSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener partes existentes o crear algunas de prueba
        $parts = Part::all();

        if ($parts->isEmpty()) {
            $this->command->warn('No hay partes en la base de datos. Creando partes de ejemplo...');
            $parts = $this->createSampleParts();
        }

        $this->command->info('Creando precios de ejemplo...');

        // Crear precios variados para las partes
        foreach ($parts->take(15) as $index => $part) {
            $this->createPriceForPart($part, $index);
        }

        $this->command->info('✅ Precios creados exitosamente!');
        $this->command->info('   - Mesa de Trabajo: ' . Price::where('workstation_type', 'table')->count());
        $this->command->info('   - Máquina: ' . Price::where('workstation_type', 'machine')->count());
        $this->command->info('   - Semi-Automática: ' . Price::where('workstation_type', 'semi_automatic')->count());
    }

    private function createSampleParts(): \Illuminate\Database\Eloquent\Collection
    {
        $partsData = [
            ['number' => 'PART-001', 'description' => 'Componente electrónico A'],
            ['number' => 'PART-002', 'description' => 'Conector tipo B'],
            ['number' => 'PART-003', 'description' => 'Cable de alimentación'],
            ['number' => 'PART-004', 'description' => 'Resistencia 10K'],
            ['number' => 'PART-005', 'description' => 'Capacitor 100uF'],
            ['number' => 'PART-006', 'description' => 'LED indicador rojo'],
            ['number' => 'PART-007', 'description' => 'Transistor NPN'],
            ['number' => 'PART-008', 'description' => 'Fusible 5A'],
            ['number' => 'PART-009', 'description' => 'Relay 12V'],
            ['number' => 'PART-010', 'description' => 'Transformador 110V'],
        ];

        foreach ($partsData as $data) {
            Part::firstOrCreate(
                ['number' => $data['number']],
                [
                    'item_number' => 'ITEM-' . substr($data['number'], -3),
                    'description' => $data['description'],
                    'unit_of_measure' => 'PCS',
                    'active' => true,
                ]
            );
        }

        return Part::all();
    }

    private function createPriceForPart(Part $part, int $index): void
    {
        // Obtener el Standard activo de la parte para usar el mismo workstation_type
        $standard = $part->standards()->where('active', true)->first();
        
        // Mapeo de StandardConfiguration workstation_type a Price workstation_type
        // StandardConfiguration: 'manual', 'machine', 'semi_automatic'
        // Price: 'table', 'machine', 'semi_automatic'
        $typeMap = [
            'manual' => 'table',
            'machine' => 'machine',
            'semi_automatic' => 'semi_automatic',
        ];
        
        // Si tiene Standard con configuración, usar ese tipo
        if ($standard) {
            $defaultConfig = $standard->configurations()->where('is_default', true)->first();
            $configType = $defaultConfig ? $defaultConfig->workstation_type : 'manual';
            $type = $typeMap[$configType] ?? 'table';
        } else {
            // Si no tiene Standard, alternar entre tipos
            $types = ['table', 'machine', 'semi_automatic'];
            $type = $types[$index % 3];
        }

        // Precio base aleatorio
        $basePrice = round(rand(50, 500) / 100, 4);

        $price = Price::firstOrCreate(
            ['part_id' => $part->id, 'active' => true],
            [
                'sample_price' => $basePrice,
                'workstation_type' => $type,
                'effective_date' => Carbon::now()->subDays(rand(1, 60)),
                'active' => true,
                'comments' => 'Precio generado por seeder - Consistente con Standard',
            ]
        );

        // Crear tiers según el tipo
        $this->createTiersForPrice($price, $basePrice, $type);
    }

    private function createTiersForPrice(Price $price, float $basePrice, string $type): void
    {
        $tierConfigs = [
            'table' => [
                ['min' => 1, 'max' => 999, 'discount' => 0],
                ['min' => 1000, 'max' => 10999, 'discount' => 0.10],
                ['min' => 11000, 'max' => 99999, 'discount' => 0.20],
                ['min' => 100000, 'max' => null, 'discount' => 0.30],
            ],
            'machine' => [
                ['min' => 1, 'max' => 9999, 'discount' => 0],
                ['min' => 10000, 'max' => 49999, 'discount' => 0.15],
                ['min' => 50000, 'max' => null, 'discount' => 0.25],
            ],
            'semi_automatic' => [
                ['min' => 2000, 'max' => 10000, 'discount' => 0],
                ['min' => 11000, 'max' => null, 'discount' => 0.15],
            ],
        ];

        foreach ($tierConfigs[$type] as $tier) {
            $tierPrice = round($basePrice * (1 - $tier['discount']), 4);
            
            $price->tiers()->firstOrCreate(
                ['min_quantity' => $tier['min'], 'max_quantity' => $tier['max']],
                ['tier_price' => $tierPrice]
            );
        }
    }
}
