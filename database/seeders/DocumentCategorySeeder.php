<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Default, global document categories (company_id = null) available to every
 * company. Categories are grouped as parent → children and are the single
 * source of truth for document "types" — nothing is hardcoded in application
 * code. "Other Document" is a standalone category that requires a custom title
 * and description at upload time.
 */
class DocumentCategorySeeder extends Seeder
{
    /** Slug marker for the standalone "Other Document" category. */
    public const OTHER_SLUG = 'other-document';

    public function run(): void
    {
        $groups = [
            'Ownership Documents' => ['Dalil', 'Bayna', 'POA'],
            'Land Records' => ['Khatian', 'Porcha', 'Mutation', 'Tax'],
            'Identity' => ['NID', 'Photo'],
            'Legal' => ['Legal Opinion', 'Court Documents'],
            'Media' => ['Photos', 'Maps'],
        ];

        $sort = 0;

        foreach ($groups as $parentName => $children) {
            $parent = DocumentCategory::firstOrCreate(
                ['company_id' => null, 'slug' => Str::slug($parentName), 'parent_id' => null],
                ['name' => $parentName, 'sort_order' => $sort++],
            );

            $childSort = 0;
            foreach ($children as $childName) {
                DocumentCategory::firstOrCreate(
                    ['company_id' => null, 'slug' => Str::slug($childName), 'parent_id' => $parent->id],
                    ['name' => $childName, 'sort_order' => $childSort++],
                );
            }
        }

        DocumentCategory::firstOrCreate(
            ['company_id' => null, 'slug' => self::OTHER_SLUG, 'parent_id' => null],
            ['name' => 'Other Document', 'sort_order' => $sort],
        );
    }
}
