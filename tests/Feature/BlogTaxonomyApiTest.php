<?php

use Kreetancraft\Blog\Enums\SeriesStatus;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Series;

test('a category landing page returns its data and SEO', function () {
    $category = Category::factory()->create(['name' => 'Trekking Tips', 'description' => 'Advice for the trail.']);

    $this->getJson(route('api.v1.categories.show', $category->slug))
        ->assertOk()
        ->assertJsonPath('data.name', 'Trekking Tips')
        ->assertJsonPath('data.seo.meta_title', 'Trekking Tips')
        ->assertJsonPath('data.seo.json_ld.0.@type', 'CollectionPage');

    $this->getJson(route('api.v1.categories.show', 'nope'))->assertNotFound();
});

test('an author landing page returns bio and SEO', function () {
    $author = Author::factory()->create(['name' => 'Pemba Sherpa']);

    $this->getJson(route('api.v1.authors.show', $author->slug))
        ->assertOk()
        ->assertJsonPath('data.name', 'Pemba Sherpa')
        ->assertJsonPath('data.seo.meta_title', 'Pemba Sherpa');
});

test('a series landing page returns detail and SEO, and hides non-active series', function () {
    $active = Series::factory()->create(['title' => 'EBC Diaries', 'status' => SeriesStatus::Active]);
    $draft = Series::factory()->create(['status' => SeriesStatus::Draft]);

    $this->getJson(route('api.v1.series.show', $active->slug))
        ->assertOk()
        ->assertJsonPath('data.title', 'EBC Diaries')
        ->assertJsonPath('data.seo.meta_title', 'EBC Diaries');

    $this->getJson(route('api.v1.series.show', $draft->slug))->assertNotFound();
});
