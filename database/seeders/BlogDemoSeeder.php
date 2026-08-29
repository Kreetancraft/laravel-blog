<?php

namespace Kreetancraft\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Kreetancraft\Blog\Enums\CommentStatus;
use Kreetancraft\Blog\Enums\PostStatus;
use Kreetancraft\Blog\Enums\SeriesStatus;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Blog\Models\Tag;

class BlogDemoSeeder extends Seeder
{
    /**
     * Seed demo authors, series, posts, and comments for local development.
     * Idempotent. BlogLookupSeeder must run first.
     */
    public function run(): void
    {
        if (app()->environment(['production', 'testing'])) {
            return;
        }

        $authors = $this->seedAuthors();
        $series = $this->seedSeries();
        $this->seedPosts($authors, $series);
    }

    /**
     * @return array<string, Author>
     */
    private function seedAuthors(): array
    {
        $authors = [];

        foreach ([
            ['name' => 'Pemba Sherpa', 'bio' => '<p>Lead guide with 15 years on the high trails of the Khumbu. Pemba has summited Everest four times and led hundreds of trekkers to Base Camp.</p>'],
            ['name' => 'Sita Gurung', 'bio' => '<p>Trek coordinator and gear specialist. Sita tests every piece of equipment we recommend before it makes a packing list.</p>'],
            ['name' => 'Rajan Thapa', 'bio' => '<p>Writer and photographer documenting the culture and landscapes of the Nepali Himalaya for over a decade.</p>'],
        ] as $data) {
            $authors[$data['name']] = Author::firstOrCreate(['name' => $data['name']], $data);
        }

        return $authors;
    }

    /**
     * @return array<string, Series>
     */
    private function seedSeries(): array
    {
        $series = [];

        foreach ([
            ['title' => 'Everest Base Camp Prep', 'status' => SeriesStatus::Active, 'description' => '<p>Everything you need to know before trekking to Everest Base Camp — fitness, gear, altitude, and logistics, in order.</p>'],
            ['title' => 'Hidden Valleys of Nepal', 'status' => SeriesStatus::Draft, 'description' => '<p>A work-in-progress series exploring Nepal\'s less-travelled trekking regions.</p>'],
        ] as $data) {
            $series[$data['title']] = Series::firstOrCreate(['title' => $data['title']], $data);
        }

        return $series;
    }

    /**
     * @param  array<string, Author>  $authors
     * @param  array<string, Series>  $series
     */
    private function seedPosts(array $authors, array $series): void
    {
        $categories = Category::pluck('id', 'name');
        $tags = Tag::pluck('id', 'name');

        $posts = [
            [
                'title' => 'The Best Time to Visit Nepal for Trekking',
                'excerpt' => 'Autumn and spring are the classic seasons — but each window has trade-offs. Here is how to pick the right month for your trek.',
                'author' => 'Pemba Sherpa',
                'status' => PostStatus::Published,
                'published_at' => now()->subDays(30),
                'is_featured' => true,
                'categories' => ['Travel Tips'],
                'tags' => ['Trekking', 'Nepal'],
                'comments' => [
                    ['author_name' => 'Emma Wilson', 'author_email' => 'emma@example.com', 'content' => 'This is exactly what I needed — booking for late October now!', 'status' => CommentStatus::Approved, 'replies' => [
                        ['author_name' => 'Pemba Sherpa', 'author_email' => 'pemba@example.com', 'content' => 'Great choice, Emma. Late October has the clearest mountain views.', 'status' => CommentStatus::Approved],
                    ]],
                    ['author_name' => 'Spam Bot', 'author_email' => 'spam@example.com', 'content' => 'Check out my discount pharmacy...', 'status' => CommentStatus::Spam],
                ],
            ],
            [
                'title' => 'Everest Base Camp Packing List: The Essentials',
                'excerpt' => 'Layering, footwear, and the ten items trekkers most often forget — a complete packing list from our guides.',
                'author' => 'Sita Gurung',
                'status' => PostStatus::Published,
                'published_at' => now()->subDays(21),
                'is_featured' => true,
                'series' => 'Everest Base Camp Prep',
                'order_in_series' => 1,
                'categories' => ['Gear Reviews', 'Travel Tips'],
                'tags' => ['Everest', 'Packing List', 'Trekking'],
                'comments' => [
                    ['author_name' => 'Liam Chen', 'author_email' => 'liam@example.com', 'content' => 'Would you recommend renting a down jacket in Kathmandu or bringing my own?', 'status' => CommentStatus::Pending],
                ],
            ],
            [
                'title' => 'Training for Altitude: A 12-Week Plan',
                'excerpt' => 'You cannot train for thin air at sea level, but you can build the endurance that makes acclimatisation easier.',
                'author' => 'Pemba Sherpa',
                'status' => PostStatus::Published,
                'published_at' => now()->subDays(14),
                'series' => 'Everest Base Camp Prep',
                'order_in_series' => 2,
                'categories' => ['Travel Tips'],
                'tags' => ['Everest', 'Safety', 'Trekking'],
                'comments' => [
                    ['author_name' => 'Noah Patel', 'author_email' => 'noah@example.com', 'content' => 'Started week one today. Will report back!', 'status' => CommentStatus::Approved],
                ],
            ],
            [
                'title' => 'Understanding Altitude Sickness on the Trail',
                'excerpt' => 'AMS symptoms, when to descend, and how our itineraries build in acclimatisation days.',
                'author' => 'Pemba Sherpa',
                'status' => PostStatus::Published,
                'published_at' => now()->subDays(10),
                'series' => 'Everest Base Camp Prep',
                'order_in_series' => 3,
                'categories' => ['Travel Tips'],
                'tags' => ['Safety', 'Everest'],
            ],
            [
                'title' => '5 Reasons to Choose the Manaslu Circuit',
                'excerpt' => 'Fewer crowds, dramatic scenery, and authentic villages — why Manaslu deserves a spot on your shortlist.',
                'author' => 'Rajan Thapa',
                'status' => PostStatus::Published,
                'published_at' => now()->subDays(7),
                'categories' => ['Destinations'],
                'tags' => ['Trekking', 'Adventure', 'Nepal'],
                'comments' => [
                    ['author_name' => 'Olivia Brown', 'author_email' => 'olivia@example.com', 'content' => 'Did the circuit last year — every word of this is true.', 'status' => CommentStatus::Approved],
                    ['author_name' => 'Anonymous', 'author_email' => 'anon@example.com', 'content' => 'Not convinced, seems overrated.', 'status' => CommentStatus::Rejected],
                ],
            ],
            [
                'title' => 'Tea House Etiquette: A First-Timer\'s Guide',
                'excerpt' => 'What to expect from Himalayan tea houses — and the small courtesies that make you a welcome guest.',
                'author' => 'Rajan Thapa',
                'status' => PostStatus::Published,
                'published_at' => now()->subDays(3),
                'categories' => ['Travel Tips', 'Destinations'],
                'tags' => ['Nepal', 'Trekking'],
            ],
            [
                'title' => 'Annapurna vs Everest: Which Trek Is Right for You?',
                'excerpt' => 'Two iconic treks, two very different experiences. We compare terrain, teahouses, crowds, and cost.',
                'author' => 'Sita Gurung',
                'status' => PostStatus::Scheduled,
                'published_at' => now()->addDays(3),
                'categories' => ['Destinations'],
                'tags' => ['Annapurna', 'Everest', 'Trekking'],
            ],
            [
                'title' => 'Monsoon Trekking: The Case for Upper Mustang',
                'excerpt' => 'Draft notes on Nepal\'s rain-shadow regions where the monsoon barely reaches.',
                'author' => 'Rajan Thapa',
                'status' => PostStatus::Draft,
                'categories' => ['Destinations'],
                'tags' => ['Adventure', 'Nepal'],
            ],
            [
                'title' => 'Our 2024 Trail Conditions Report',
                'excerpt' => 'An archived season report kept for reference.',
                'author' => 'Pemba Sherpa',
                'status' => PostStatus::Archived,
                'published_at' => now()->subMonths(18),
                'categories' => ['Travel Tips'],
                'tags' => ['Trekking'],
            ],
        ];

        foreach ($posts as $data) {
            $post = Post::firstOrCreate(
                ['title' => $data['title']],
                [
                    'excerpt' => $data['excerpt'],
                    'content' => $this->demoContent($data['title']),
                    'status' => $data['status'],
                    'published_at' => $data['published_at'] ?? null,
                    'author_id' => $authors[$data['author']]->id,
                    'series_id' => isset($data['series']) ? $series[$data['series']]->id : null,
                    'order_in_series' => $data['order_in_series'] ?? null,
                    'is_featured' => $data['is_featured'] ?? false,
                ],
            );

            $post->categories()->syncWithoutDetaching($categories->only($data['categories'])->values());
            $post->tags()->syncWithoutDetaching($tags->only($data['tags'])->values());

            if ($post->title === 'The Best Time to Visit Nepal for Trekking') {
                $post->seoMeta()->firstOrCreate([], [
                    'meta_title' => 'Best Time to Visit Nepal | HTV Guide',
                    'meta_description' => 'When is the best season for trekking in Nepal? Learn about spring rhododendrons and autumn mountain views.',
                    'focus_keyword' => 'best time to visit nepal',
                ]);
            }

            if ($post->title === 'Everest Base Camp Packing List: The Essentials') {
                $post->seoMeta()->firstOrCreate([], [
                    'meta_title' => 'Everest Base Camp Packing List | Essential Gear',
                    'meta_description' => 'A complete guide to Everest Base Camp trekking gear, including layering, footwear, and altitude health essentials.',
                    'focus_keyword' => 'everest base camp packing list',
                ]);
            }

            foreach ($data['comments'] ?? [] as $commentData) {
                $replies = $commentData['replies'] ?? [];
                unset($commentData['replies']);

                $comment = $post->comments()->firstOrCreate(
                    ['author_email' => $commentData['author_email'], 'content' => $commentData['content']],
                    $commentData,
                );

                foreach ($replies as $replyData) {
                    $post->comments()->firstOrCreate(
                        ['author_email' => $replyData['author_email'], 'content' => $replyData['content']],
                        [...$replyData, 'parent_id' => $comment->id],
                    );
                }
            }
        }
    }

    private function demoContent(string $title): string
    {
        return '<h2>'.$title.'</h2>'
            .'<p>The Himalaya reward preparation. This guide walks through what our guides have learned across hundreds of departures, from the first day on the trail to the highest pass.</p>'
            .'<p>Every trek is different, but the fundamentals stay the same: walk slowly, drink more water than you think you need, and listen to your guide. The mountains have been here for millions of years — they are not going anywhere, and neither should your itinerary margins.</p>'
            .'<h3>Plan with the seasons</h3>'
            .'<p>Autumn (October–November) brings the clearest skies, while spring (March–May) trades a little haze for rhododendron forests in full bloom. Winter treks are possible at lower elevations, and the monsoon opens the rain-shadow valleys.</p>'
            .'<p>Whatever you choose, build in buffer days. Mountain weather sets its own schedule, and the best trips are the ones with room to adapt.</p>';
    }
}
