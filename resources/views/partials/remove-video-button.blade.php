{{--
  Owner-facing "remove this video" control for the guest pages.

  Expects:
    $schedule  - the Role whose guest page is being viewed (provides the subdomain to post to)
    $target    - the Role the video actually belongs to (often a talent on someone else's page)
    $videoUrl  - the stored url, VERBATIM off decodeLinks(). Do not re-canonicalise it here: the
                 column holds URLs written by two different code paths that normalise differently,
                 and the endpoint falls back to an exact-string match for entries that are not
                 parseable YouTube URLs.
    $class     - optional wrapper classes

  A real form POST, not fetch(): these routes sit behind the app_subdomain middleware, so a request
  from a guest host is 302'd to app.<domain> - which fetch() would follow as a bodyless GET. The
  action therefore goes through app_url(). Confirmation is the app-wide delegated
  form[data-confirm] handler in layouts/app.blade.php, so this needs no JavaScript of its own.
--}}
@if (! empty($videoUrl) && \App\Utils\VideoUtils::canRemoveVideo(auth()->user(), $target, $schedule))
  <form method="POST"
        action="{{ app_url(route('role.remove_video', ['subdomain' => $schedule->subdomain], false)) }}"
        data-confirm="{{ __('messages.are_you_sure_remove_video') }}"
        class="{{ $class ?? '' }}">
    @csrf
    <input type="hidden" name="role_hash" value="{{ \App\Utils\UrlUtils::encodeId($target->id) }}">
    <input type="hidden" name="video_url" value="{{ $videoUrl }}">
    <button type="submit"
            class="inline-flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400 hover:underline focus:outline-none focus:ring-2 focus:ring-red-500 rounded">
      <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
      </svg>
      {{ __('messages.remove_video') }}
    </button>
  </form>
@endif
