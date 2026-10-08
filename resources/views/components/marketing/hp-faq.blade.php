{{--
    The questions of a page in the house style (partials/hp-kit): the heading stays beside them
    from a laptop up, and one answer may stand open.

    Props:
      items   [['q' => ..., 'a' => ...], ...], the same array the page hands <x-seo.faq-schema>
      kicker  the small label over the heading
      lead    a sentence under the heading
      open    the index of the answer that stands open, or null
      html    true when the answers are the page author's own markup (links); they are then
              printed as they are, so never pass text a visitor wrote
    The slot is the heading.
--}}
@props([
    'items',
    'kicker' => 'Before you ask',
    'lead' => null,
    'open' => null,
    'html' => false,
])
<section {{ $attributes->class(['hp-sec']) }}>
    <div class="hp-wrap">
        <div class="hp-faq-grid">
            <div class="hp-head">
                <span class="hp-kicker" data-reveal>{{ $kicker }}</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">{{ $slot }}</h2>
                @if ($lead)
                    <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">{{ $lead }}</p>
                @endif
                {{ $after ?? '' }}
            </div>
            <div data-reveal-group="60">
                @foreach ($items as $item)
                    @php
                        $faqQuestion = $item['q'] ?? $item['question'];
                        $faqAnswer = $item['a'] ?? $item['answer'];
                    @endphp
                    <details name="faq" data-reveal class="hp-faq" @if ($open === $loop->index) open @endif>
                        <summary>
                            <h3>{{ $faqQuestion }}</h3>
                            <i aria-hidden="true"></i>
                        </summary>
                        <div class="faq-answer">
                            @if ($html)
                                {!! $faqAnswer !!}
                            @else
                                <p>{{ $faqAnswer }}</p>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </div>
</section>
