<?php

namespace Kreetancraft\Blog\Database\Seeders;

use Illuminate\Database\Seeder;

class BlogsDatabaseSeeder extends Seeder
{
    /**
     * Seed the entire Blogs module: reference lookups (every environment) and
     * demo content (local/dev only — guarded inside BlogDemoSeeder).
     */
    public function run(): void
    {
        $this->call([
            BlogLookupSeeder::class,
            BlogDemoSeeder::class,
        ]);
    }
}
