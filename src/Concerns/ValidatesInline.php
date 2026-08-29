<?php

namespace Kreetancraft\Blog\Concerns;

use Illuminate\Validation\ValidationException;

trait ValidatesInline
{
    /**
     * Fields that have individually passed validation.
     *
     * @var array<int, string>
     */
    public array $validFields = [];

    /**
     * True once any bound field has been synced since load/save.
     */
    public bool $formDirty = false;

    /**
     * Cached validation rules to avoid rebuilding on every property update.
     *
     * @var array<string, mixed>
     */
    protected array $cachedRules = [];

    /**
     * Livewire lifecycle hook — called for every property update.
     */
    public function updated(string $property, mixed $value): void
    {
        $this->formDirty = true;

        if (! $this->hasInlineRuleFor($property)) {
            return;
        }

        try {
            $this->validateOnly($property);

            $this->validFields = array_values(array_unique([...$this->validFields, $property]));
        } catch (ValidationException $e) {
            $this->validFields = array_values(array_diff($this->validFields, [$property]));

            throw $e;
        }
    }

    public function isFieldValid(string $property): bool
    {
        return in_array($property, $this->validFields, true);
    }

    protected function resetInlineValidation(): void
    {
        $this->validFields = [];
        $this->formDirty = false;
    }

    /**
     * Cache rules on first access to avoid rebuilding on every property update.
     */
    protected function getCachedRules(): array
    {
        if ($this->cachedRules === []) {
            $this->cachedRules = $this->getRules();
        }

        return $this->cachedRules;
    }

    protected function hasInlineRuleFor(string $property): bool
    {
        $keys = array_keys($this->getCachedRules());

        if (in_array($property, $keys, true)) {
            return true;
        }

        $wildcard = preg_replace('/\.\d+/', '.*', $property);

        return in_array($wildcard, $keys, true);
    }
}
