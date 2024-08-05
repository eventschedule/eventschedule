<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class SquareImage implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$value->isValid()) {
            return false;
        }

        // Get image dimensions
        $image = getimagesize($value->getRealPath());
        if (!$image) {
            return false;
        }

        $width = $image[0];
        $height = $image[1];

        return $width == $height;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a square image.';
    }
}
