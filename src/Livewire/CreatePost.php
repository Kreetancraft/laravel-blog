<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Kreetancraft\Blog\Actions\CreatePostAction;
use Kreetancraft\Blog\Concerns\ValidatesInline;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Livewire\Concerns\InteractsWithPostForm;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Routes;
use Livewire\Attributes\Title;
use Livewire\Component;

class CreatePost extends Component
{
    use InteractsWithPostForm;
    use ValidatesInline;

    public function mount(): void
    {
        $this->authorize('create', Post::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return $this->postRules();
    }

    public function save(): void
    {
        $this->authorize('create', Post::class);

        $this->validate();
        $this->guardPublishing();

        $post = CreatePostAction::run($this->postData());

        $this->resetInlineValidation();

        Flux::toast(variant: 'success', text: __('Post created.'));

        $this->redirect(Routes::to('posts.edit', $post), navigate: true);
    }

    #[Title('New Post - Admin')]
    public function render()
    {
        return view('blog::livewire.create-post', array_merge($this->postFormOptions(), [
            'seoAnalysis' => $this->seoAnalysis(),
        ]))->layout(Layout::admin());
    }
}
