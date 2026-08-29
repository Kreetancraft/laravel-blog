<?php

namespace Kreetancraft\Blog\Livewire;

use Flux\Flux;
use Kreetancraft\Blog\Actions\UpdateSeriesAction;
use Kreetancraft\Blog\Concerns\ValidatesInline;
use Kreetancraft\Blog\Layout;
use Kreetancraft\Blog\Livewire\Concerns\InteractsWithSeriesForm;
use Kreetancraft\Blog\Models\Series;
use Livewire\Attributes\Title;
use Livewire\Component;

class EditSeries extends Component
{
    use InteractsWithSeriesForm;
    use ValidatesInline;

    public Series $series;

    public function mount(Series $series): void
    {
        $this->authorize('update', $series);

        $this->series = $series;
        $this->title = $series->title;
        $this->description = (string) $series->description;
        $this->status = $series->status->value;
        $this->coverMedia = $series->imageList((string) config('blog.collections.series_cover', 'cover'));

        $this->fillSeoForm($series);
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
        $this->authorize('update', $this->series);

        $this->validate();

        UpdateSeriesAction::run($this->series, $this->seriesData());

        $this->series->refresh();

        $this->resetInlineValidation();

        Flux::toast(variant: 'success', text: __('Series saved.'));
    }

    #[Title('Edit Series - Admin')]
    public function render()
    {
        return view('blog::livewire.edit-series', [
            'seriesPosts' => $this->series->posts()->with('author')->get(),
            'seoAnalysis' => $this->seoAnalysis(),
        ])->layout(Layout::admin());
    }
}
