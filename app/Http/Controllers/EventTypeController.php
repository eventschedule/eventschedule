<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request->user());

        $eventTypes = EventType::ordered();
        $locales = config('app.supported_languages', []);
        $fallbackLocale = config('app.fallback_locale');

        if ($fallbackLocale && ! in_array($fallbackLocale, $locales, true)) {
            $locales[] = $fallbackLocale;
        }

        if (! in_array('en', $locales, true)) {
            $locales[] = 'en';
        }

        $locales = array_values(array_unique($locales));
        sort($locales);

        $translationLocales = array_values(array_filter($locales, fn ($locale) => $locale !== 'en'));

        return view('settings.event-types', [
            'eventTypes' => $eventTypes,
            'locales' => $translationLocales,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $validated = $request->validateWithBag('createEventType', [
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'translations' => ['sometimes', 'array'],
            'translations.*' => ['nullable', 'string', 'max:255'],
        ]);

        $translations = $request->input('translations', []);
        $sortOrder = array_key_exists('sort_order', $validated)
            ? (int) $validated['sort_order']
            : ((int) EventType::query()->max('sort_order')) + 1;

        EventType::create([
            'name' => $validated['name'],
            'slug' => EventType::generateUniqueSlug($validated['name']),
            'translations' => EventType::buildTranslations($validated['name'], $translations),
            'sort_order' => $sortOrder,
        ]);

        return redirect()
            ->route('settings.event_types.index')
            ->with('status', 'event-type-created');
    }

    public function update(Request $request, EventType $eventType): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $validated = $request->validateWithBag('eventType-' . $eventType->id, [
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'translations' => ['sometimes', 'array'],
            'translations.*' => ['nullable', 'string', 'max:255'],
        ]);

        $sortOrder = array_key_exists('sort_order', $validated)
            ? (int) $validated['sort_order']
            : $eventType->sort_order;

        $translations = $request->input('translations', []);

        $eventType->update([
            'name' => $validated['name'],
            'slug' => EventType::generateUniqueSlug($validated['name'], $eventType->id),
            'translations' => EventType::buildTranslations($validated['name'], $translations),
            'sort_order' => $sortOrder,
        ]);

        return redirect()
            ->route('settings.event_types.index')
            ->with('status', 'event-type-updated');
    }

    public function destroy(Request $request, EventType $eventType): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $inUse = Event::query()->where('category_id', $eventType->id)->exists();

        if ($inUse) {
            return back()->withErrors([
                'delete' => __('messages.event_type_delete_in_use'),
            ], 'eventType-' . $eventType->id);
        }

        $eventType->delete();

        return redirect()
            ->route('settings.event_types.index')
            ->with('status', 'event-type-deleted');
    }

    protected function authorizeAdmin($user): void
    {
        abort_unless($user && method_exists($user, 'isAdmin') && $user->isAdmin(), 403);
    }
}
