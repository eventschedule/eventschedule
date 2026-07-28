{{--
    One FAQ row.

    Slot-based rather than an array, because answers routinely contain links,
    .doc-inline-code and <strong>.

    name="doc-faq" makes the group exclusive natively, with no JavaScript.
    Ships closed: the FAQPage JSON-LD already on these pages carries the SEO,
    and browsers auto-expand a <details> when find-in-page hits inside it.
--}}
@props(['question', 'group' => 'doc-faq', 'open' => false])

<details name="{{ $group }}" class="doc-faq" @if ($open) open @endif>
    <summary>
        <span>{{ $question }}</span>
        <svg class="doc-faq-chev" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </summary>
    <div class="doc-faq-body">{{ $slot }}</div>
</details>
