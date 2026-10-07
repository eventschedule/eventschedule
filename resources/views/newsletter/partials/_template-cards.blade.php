{{-- The saved templates, as cards: a template is chosen by how it looks, so each shows the
     colours it was saved with (partials/_template-swatch) over its name, its design and the day
     it was made. The same grid on the schedule owner's page and the platform admin's; they used
     to be full-width panels with three loose links at the end.

     Expects $templates, and three closures that give a template's addresses: $useUrl (start a
     newsletter from it), $editUrl, $deleteUrl. --}}
<div class="news-templates">
    @foreach ($templates as $template)
    <article class="ap-card rounded-xl news-template">
        <a href="{{ $useUrl($template) }}" class="news-template-face" tabindex="-1" aria-hidden="true">
            @include('newsletter.partials._template-swatch', ['swatchOf' => $template])
        </a>
        <div class="news-template-text">
            <h3 class="news-template-name"><bdi>{{ $template->name }}</bdi></h3>
            <p class="news-template-meta"><span class="capitalize">{{ $template->template }}</span> &middot; {{ $template->created_at->translatedFormat('M j, Y') }}</p>
        </div>
        <div class="news-template-foot">
            <a href="{{ $useUrl($template) }}" class="event-link">{{ __('messages.use') }}</a>
            <a href="{{ $editUrl($template) }}" class="event-link">{{ __('messages.edit') }}</a>
            <form method="POST" action="{{ $deleteUrl($template) }}" class="js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="event-link is-danger">{{ __('messages.delete') }}</button>
            </form>
        </div>
    </article>
    @endforeach
</div>
