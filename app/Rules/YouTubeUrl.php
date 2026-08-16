<?php

namespace App\Rules;

use App\Utils\UrlUtils;
use Illuminate\Contracts\Validation\Rule;

/**
 * A URL the app can actually turn into a YouTube embed.
 *
 * `youtube_links` had no host check on any of its write paths, so a non-YouTube URL could be
 * stored and would then render as an empty iframe on the guest pages - UrlUtils::getYouTubeEmbed()
 * returns false for it and Blade prints that as an empty string.
 */
class YouTubeUrl implements Rule
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
        return is_string($value) && UrlUtils::extractYouTubeVideoId($value) !== null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('messages.invalid_youtube_url');
    }
}
