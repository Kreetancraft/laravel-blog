<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'blog_posts' => 'Kreetancraft\Blog\Models\Post',
            'blog_categories' => 'Kreetancraft\Blog\Models\Category',
            'blog_authors' => 'Kreetancraft\Blog\Models\Author',
            'blog_series' => 'Kreetancraft\Blog\Models\Series',
        ];

        foreach ($tables as $table => $modelClass) {
            DB::table($table)->orderBy('id')->chunk(100, function ($items) use ($table, $modelClass) {
                foreach ($items as $item) {
                    $hasMeta = ! empty($item->meta_title)
                        || ! empty($item->meta_description)
                        || ! empty($item->meta_keywords)
                        || ($table === 'blog_posts' && ! empty($item->canonical_url));

                    if ($hasMeta) {
                        DB::table('seo_meta')->updateOrCreate([
                            'seoable_type' => $modelClass,
                            'seoable_id' => $item->id,
                        ], [
                            'meta_title' => $item->meta_title ?? null,
                            'meta_description' => $item->meta_description ?? null,
                            'meta_keywords' => $item->meta_keywords ?? null,
                            'canonical_url' => ($table === 'blog_posts') ? ($item->canonical_url ?? null) : null,
                            'created_at' => $item->created_at ?? now(),
                            'updated_at' => $item->updated_at ?? now(),
                        ]);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'blog_posts' => 'Kreetancraft\Blog\Models\Post',
            'blog_categories' => 'Kreetancraft\Blog\Models\Category',
            'blog_authors' => 'Kreetancraft\Blog\Models\Author',
            'blog_series' => 'Kreetancraft\Blog\Models\Series',
        ];

        foreach ($tables as $table => $modelClass) {
            DB::table('seo_meta')
                ->where('seoable_type', $modelClass)
                ->orderBy('id')
                ->chunk(100, function ($metas) use ($table) {
                    foreach ($metas as $meta) {
                        $update = [
                            'meta_title' => $meta->meta_title,
                            'meta_description' => $meta->meta_description,
                            'meta_keywords' => $meta->meta_keywords,
                        ];

                        if ($table === 'blog_posts') {
                            $update['canonical_url'] = $meta->canonical_url;
                        }

                        DB::table($table)
                            ->where('id', $meta->seoable_id)
                            ->update($update);
                    }
                });
        }
    }
};
