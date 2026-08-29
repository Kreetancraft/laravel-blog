<?php

namespace Kreetancraft\Blog\Actions;

use Illuminate\Support\Facades\Log;
use Kreetancraft\Blog\Contracts\BlogsContract;
use Lorisleiva\Actions\Concerns\AsAction;

class PublishScheduledPostsAction
{
    use AsAction;

    public function __construct(
        private readonly BlogsContract $blogs,
    ) {}

    public function handle(): int
    {
        $count = $this->blogs->publishScheduledPosts();

        if ($count > 0) {
            Log::info('Scheduled blog posts published', [
                'count' => $count,
            ]);
        }

        return $count;
    }
}
