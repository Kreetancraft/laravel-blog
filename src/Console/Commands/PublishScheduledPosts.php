<?php

namespace Kreetancraft\Blog\Console\Commands;

use Illuminate\Console\Command;
use Kreetancraft\Blog\Actions\PublishScheduledPostsAction;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blogs:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled blog posts whose publish time has arrived';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = PublishScheduledPostsAction::run();

        $this->info($count === 0
            ? 'No scheduled posts are due.'
            : "Published {$count} scheduled post(s).");

        return Command::SUCCESS;
    }
}
