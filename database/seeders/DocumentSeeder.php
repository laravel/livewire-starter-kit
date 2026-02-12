<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Document;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $categories = Category::all()->keyBy('category_name');
        
        $documents = [
            [
                'category_name' => 'Presentations',
                'document_name' => 'Q4 2024 Business Review',
                'url' => 'https://example.com/presentations/q4-2024-review',
                'is_admin' => false,
            ],
            [
                'category_name' => 'Presentations',
                'document_name' => 'Product Launch Presentation',
                'url' => 'https://example.com/presentations/product-launch',
                'is_admin' => false,
            ],
            [
                'category_name' => 'Presentations',
                'document_name' => 'Annual Company Meeting',
                'url' => 'https://example.com/presentations/annual-meeting-2024',
                'is_admin' => false,
            ],
            [
                'category_name' => 'Documents',
                'document_name' => 'Company Policy Handbook',
                'url' => 'https://example.com/documents/policy-handbook',
                'is_admin' => false,
            ],
            [
                'category_name' => 'Reports',
                'document_name' => 'Monthly Sales Report',
                'url' => 'https://example.com/reports/monthly-sales',
                'is_admin' => false,
            ],
            [
                'category_name' => 'Images',
                'document_name' => 'Company Logo',
                'url' => 'https://example.com/images/company-logo',
                'is_admin' => false,
            ],
            [
                'category_name' => 'Videos',
                'document_name' => 'Training Video 1',
                'url' => 'https://example.com/videos/training-1',
                'is_admin' => false,
            ],
            [
                'category_name' => 'Code',
                'document_name' => 'Project Source Code',
                'url' => 'https://github.com/example/project',
                'is_admin' => false,
            ],
        ];

        foreach ($documents as $docData) {
            $category = $categories->get($docData['category_name']);
            
            if ($category) {
                Document::create([
                    'category_id' => $category->id,
                    'category_name' => $docData['category_name'],
                    'document_name' => $docData['document_name'],
                    'url' => $docData['url'],
                    'is_admin' => $docData['is_admin'] ?? false,
                ]);
            }
        }
    }
}
