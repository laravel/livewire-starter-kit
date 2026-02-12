<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category_name' => 'Documents',
                'description' => 'General documents and files',
                'color' => '#3B82F6',
                'icon' => 'folder'
            ],
            [
                'category_name' => 'Reports',
                'description' => 'Business and financial reports',
                'color' => '#10B981',
                'icon' => 'file'
            ],
            [
                'category_name' => 'Presentations',
                'description' => 'Slide decks and presentations',
                'color' => '#F59E0B',
                'icon' => 'archive'
            ],
            [
                'category_name' => 'Images',
                'description' => 'Photos and graphics',
                'color' => '#EF4444',
                'icon' => 'image'
            ],
            [
                'category_name' => 'Videos',
                'description' => 'Video files and recordings',
                'color' => '#8B5CF6',
                'icon' => 'video'
            ],
            [
                'category_name' => 'Code',
                'description' => 'Source code and scripts',
                'color' => '#6B7280',
                'icon' => 'code'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
