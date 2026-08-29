<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Kreetancraft\Blog\Actions\UpdatePostAction;
use Kreetancraft\Blog\Concerns\ValidatesInline;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Livewire\Concerns\InteractsWithPostForm;
use Kreetancraft\Blog\Models\Post;
use Livewire\Attributes\Title;
use Livewire\Component;

class EditPost extends Component
{
    use InteractsWithPostForm;
    use ValidatesInline;

    public Post $post;

    public function mount(Post $post): void
    {
        $this->authorize('update', $post);

        $this->post = $post;
        $this->title = $post->title;
        $this->slug = (string) $post->slug;
        $this->excerpt = (string) $post->excerpt;
        $this->content = (string) $post->content;
        $this->status = $post->status->value;
        $this->published_at = $post->published_at?->format('Y-m-d\TH:i');
        $this->author_id = $post->author_id;
        $this->series_id = $post->series_id;
        $this->order_in_series = $post->order_in_series;
        $this->is_featured = $post->is_featured;
        $this->categories = $post->categories()->pluck('blog_categories.id')->all();
        $this->tags = $post->tags()->pluck('blog_tags.id')->all();
        $this->featuredMedia = $post->imageList((string) config('blog.collections.featured', 'featured'));

        $this->fillSeoForm($post);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return $this->postRules($this->post->id);
    }

    public function save(): void
    {
        $this->authorize('update', $this->post);

        $this->validate();

        // Only gate the publish permission when the status is being changed
        // into a publishing state; already-published posts stay editable.
        if ($this->status !== $this->post->status->value) {
            $this->guardPublishing();
        }

        UpdatePostAction::run($this->post, $this->postData());

        $this->post->refresh();
        $this->slug = (string) $this->post->slug;

        $this->resetInlineValidation();

        Flux::toast(variant: 'success', text: __('Post saved.'));
    }

    #[Title('Edit Post - Admin')]
    public function render()
    {
        return view('blog::livewire.edit-post', array_merge($this->postFormOptions(), [
            'seoAnalysis' => $this->seoAnalysis(),
        ]))->layout(Layout::admin());
    }
}
