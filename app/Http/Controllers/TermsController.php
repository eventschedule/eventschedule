<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Utils\MarkdownUtils;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Throwable;

class TermsController extends Controller
{
    public function show(): View
    {
        $storedGeneralSettings = [];

        try {
            $storedGeneralSettings = Setting::forGroup('general');
        } catch (Throwable $exception) {
            $storedGeneralSettings = [];
        }

        $storedMarkdown = $storedGeneralSettings['terms_markdown'] ?? null;

        $termsHtml = $storedGeneralSettings['terms_html'] ?? null;

        if ($termsHtml === null && $storedMarkdown !== null) {
            $termsHtml = MarkdownUtils::convertToHtml($storedMarkdown);
        }

        if ($termsHtml === null) {
            $termsHtml = MarkdownUtils::convertToHtml(config('terms.default_markdown'));
        }

        $lastUpdatedRaw = $storedGeneralSettings['terms_updated_at']
            ?? config('terms.default_last_updated');

        $lastUpdated = null;

        if (! empty($lastUpdatedRaw)) {
            try {
                $lastUpdated = Carbon::parse($lastUpdatedRaw);
            } catch (Throwable $exception) {
                $lastUpdated = null;
            }
        }

        return view('terms.show', [
            'termsHtml' => $termsHtml,
            'lastUpdated' => $lastUpdated,
        ]);
    }
}
