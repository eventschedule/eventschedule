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
        protected string $comparisonColor = '#FFFFFF'
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = ColorUtils::normalizeHexColor(is_string($value) ? $value : null);

        if ($normalized === null) {
            $fail(trans('messages.branding_color_invalid', ['attribute' => $this->label]));

            return;
        }

        $contrast = ColorUtils::contrastRatio($normalized, $this->comparisonColor);

        if ($contrast === null || $contrast < $this->minimumContrast) {
            $fail(trans('messages.branding_color_contrast_error', [
                'attribute' => $this->label,
                'minimum' => number_format($this->minimumContrast, 1),
                'ratio' => $contrast === null ? 'N/A' : number_format($contrast, 2),
            ]));
        }
    }
}
