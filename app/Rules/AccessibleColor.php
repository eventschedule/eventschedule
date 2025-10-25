<?php

namespace App\Rules;

use App\Support\ColorUtils;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AccessibleColor implements ValidationRule
{
    public function __construct(
        protected string $label,
        protected float $minimumContrast = 4.5,
        array $comparisonColors = ['#FFFFFF']
    ) {
        $this->comparisonColors = $this->prepareComparisonColors($comparisonColors);
    }

    protected array $comparisonColors;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = ColorUtils::normalizeHexColor(is_string($value) ? $value : null);

        if ($normalized === null) {
            $fail(trans('messages.branding_color_invalid', ['attribute' => $this->label]));

            return;
        }

        $best = $this->bestContrast($normalized);

        if ($best === null || $best['ratio'] < $this->minimumContrast) {
            $choices = collect($this->comparisonColors)
                ->map(fn (array $candidate) => $candidate['label'] ?? $candidate['color'])
                ->implode(', ');

            $fail(trans('messages.branding_color_contrast_error', [
                'attribute' => $this->label,
                'minimum' => number_format($this->minimumContrast, 1),
                'ratio' => $best === null ? 'N/A' : number_format($best['ratio'], 2),
                'choices' => $choices,
            ]));
        }
    }

    protected function prepareComparisonColors(array $comparisonColors): array
    {
        $prepared = [];

        foreach ($comparisonColors as $key => $value) {
            $color = is_int($key) ? $value : $key;
            $label = is_int($key) ? null : (is_string($value) ? $value : null);

            if (! is_string($color)) {
                continue;
            }

            $normalized = ColorUtils::normalizeHexColor($color);

            if ($normalized === null) {
                continue;
            }

            $prepared[] = [
                'color' => $normalized,
                'label' => $label,
            ];
        }

        if ($prepared === []) {
            $prepared[] = [
                'color' => '#FFFFFF',
                'label' => null,
            ];
        }

        return $prepared;
    }

    protected function bestContrast(string $color): ?array
    {
        $best = null;

        foreach ($this->comparisonColors as $candidate) {
            $ratio = ColorUtils::contrastRatio($color, $candidate['color']);

            if ($ratio === null) {
                continue;
            }

            if ($best === null || $ratio > $best['ratio']) {
                $best = $candidate + ['ratio' => $ratio];
            }
        }

        return $best;
    }
}
