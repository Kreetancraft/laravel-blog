<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Kreetancraft\Blog\Actions\CreateSeriesAction;
use Kreetancraft\Blog\Concerns\ValidatesInline;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Livewire\Concerns\InteractsWithSeriesForm;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Blog\Routes;
use Livewire\Attributes\Title;
use Livewire\Component;

class CreateSeries extends Component
{
    use InteractsWithSeriesForm;
    use ValidatesInline;

    public function mount(): void
    {
        $this->authorize('create', Series::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return $this->seriesRules();
    }

    public function save(): void
    {
        $this->authorize('create', Series::class);

        $this->validate();

        CreateSeriesAction::run($this->seriesData());

        $this->resetInlineValidation();

        Flux::toast(variant: 'success', text: __('Series created.'));

        $this->redirect(Routes::to('series'), navigate: true);
    }

    #[Title('New Series - Admin')]
    public function render()
    {
        return view('blog::livewire.create-series', [
            'seoAnalysis' => $this->seoAnalysis(),
        ])->layout(Layout::admin());
    }
}
