{{--
    A quotation from this application's own source (App\Utils\SourceExcerpt), for /open-source:
    the path, the line numbers as they stand in the copy that is running, the lines, and the
    ones that prove the claim marked.

    Props:
      excerpt  what SourceExcerpt returned, or null (then nothing is printed: the claim stands
               without its code, and OpenSourcePageTest says so)
      note     one sentence under the lines, about the marked ones (or the slot, for one
               that carries markup)
      live     false to leave "read from this server" off the heading
      tools    a slot at the end of the heading, for a control that belongs to this quotation
--}}
@props(['excerpt', 'note' => null, 'live' => true])
@if ($excerpt)
    <figure {{ $attributes->class(['os-code']) }}>
        <figcaption class="os-code-head">
            <a href="{{ $excerpt['url'] }}" target="_blank" rel="noopener noreferrer" class="os-path">{!! implode('/<wbr>', array_map(fn ($part) => '<span class="os-nb">'.e($part).'</span>', explode('/', $excerpt['path']))) !!}</a>
            <span>{{ $excerpt['first'] === $excerpt['last'] ? 'line '.number_format($excerpt['first']) : 'lines '.number_format($excerpt['first']).' to '.number_format($excerpt['last']) }}</span>
            @if ($live)
                <span class="os-code-live"><i aria-hidden="true"></i>read from this server</span>
            @endif
            {{ $tools ?? '' }}
        </figcaption>
        {{-- Code reads left to right whatever language the page is in. --}}
        <div class="os-code-body" dir="ltr">
            @foreach ($excerpt['lines'] as $line)
                <div @class(['os-line', 'is-marked' => $line['mark'] !== null]) @if ($line['mark']) data-mark="{{ $line['mark'] }}" @endif><i class="os-ln" aria-hidden="true">{{ $line['n'] }}</i><code style="--in: {{ $line['in'] }};">{!! $line['html'] !== '' ? $line['html'] : '&nbsp;' !!}</code></div>
            @endforeach
        </div>
        @if ($note)
            <p class="os-code-note">{{ $note }}</p>
        @elseif (trim($slot) !== '')
            <p class="os-code-note">{{ $slot }}</p>
        @endif
    </figure>
@endif
