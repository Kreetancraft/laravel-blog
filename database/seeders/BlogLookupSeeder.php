<?php

namespace Kreetancraft\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Tag;

class BlogLookupSeeder extends Seeder
{
    /**
     * Seed the baseline categories and tags. Idempotent and safe for
     * every environment.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Travel Tips', 'description' => 'Practical advice for planning and enjoying your trip to Nepal.'],
            ['name' => 'Gear Reviews', 'description' => 'Hands-on reviews of trekking gear, clothing, and equipment.'],
            ['name' => 'Destinations', 'description' => 'Guides to the regions, trails, and places we trek.'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }

        $tags = ['Trekking', 'Everest', 'Annapurna', 'Nepal', 'Adventure', 'Packing List', 'Safety'];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(['name' => $tag]);
        }
    }
}
