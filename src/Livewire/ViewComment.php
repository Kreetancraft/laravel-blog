<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Kreetancraft\Blog\Enums\CommentStatus;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Models\Comment;
use Kreetancraft\Blog\Routes;
use Livewire\Attributes\Title;
use Livewire\Component;
use SanderMuller\FluentValidation\FluentRule as Rule;

class ViewComment extends Component
{
    public Comment $comment;

    public string $replyContent = '';

    public function mount(Comment $comment): void
    {
        $this->authorize('view', $comment);

        $this->comment = $comment->load([
            'post', 'parent.user', 'user', 'replies.user',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'replyContent' => Rule::string()->required()->max(5000),
        ];
    }

    public function sendReply(): void
    {
        $this->authorize('moderate', $this->comment);
        $this->validate();

        Comment::create([
            'post_id' => $this->comment->post_id,
            'parent_id' => $this->comment->parent_id ?? $this->comment->id,
            'user_id' => auth()->id(),
            'content' => $this->replyContent,
            'status' => CommentStatus::Approved,
        ]);

        if ($this->comment->status === CommentStatus::Pending) {
            $this->comment->update(['status' => CommentStatus::Approved]);
        }

        $this->replyContent = '';
        $this->comment->load(['replies.user']);
        Flux::toast(variant: 'success', text: __('Reply posted.'));
    }

    public function setStatus(string $status): void
    {
        $this->authorize('moderate', $this->comment);

        $this->comment->update(['status' => CommentStatus::from($status)]);

        Flux::toast(variant: 'success', text: __('Comment updated.'));
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->comment);

        $this->comment->delete();

        Flux::toast(variant: 'success', text: __('Comment deleted.'));

        $this->redirect(Routes::to('comments'), navigate: true);
    }

    #[Title('Comment Detail - Admin')]
    public function render()
    {
        return view('blog::livewire.view-comment', [
            'ipCommentCount' => $this->comment->ip_address
                ? Comment::where('ip_address', $this->comment->ip_address)->count()
                : 0,
        ])->layout(Layout::admin());
    }
}
