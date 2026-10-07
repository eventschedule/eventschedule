@props([
    // Which flashed keys this page prints, and in what tone: ['success' => 'success', 'error' => 'error'].
    // Name them. A page prints only what it used to print, at a place that is on screen when the
    // page opens: a key left out here is still the layout's to toast.
    'keys' => ['success' => 'success'],
])

{{-- What the last request said, as a notice on the page (x-page-notice), for a page of the admin
     portal. layouts/app toasts `message`, `warning` and `error` on its own; a page that also
     printed one said the same thing twice, a red toast over a red box for ten seconds. For each
     key this component has printed it tells the layout, and the toast for THAT key stands down;
     any other key is still toasted.

     The note is left on the request, never shared with the view factory: the factory outlives a
     request inside a test, and a later page would have lost its toast.

     Put it where it is seen when the page opens (under the title). A redirect that lands on a
     fragment far down the page (a settings card) wants the toast: leave its key out. --}}
@php
    $shown = [];
    foreach ($keys as $key => $tone) {
        if (is_string(session($key)) && session($key) !== '') {
            $shown[$key] = [$tone, session($key)];
        }
    }
    if ($shown) {
        request()->attributes->set('pageFlashShown', array_merge(request()->attributes->get('pageFlashShown', []), array_keys($shown)));
    }
@endphp

@foreach ($shown as [$tone, $text])
<x-page-notice :tone="$tone" :role="$tone === 'error' ? 'alert' : 'status'" {{ $attributes }}><span v-pre>{{ $text }}</span></x-page-notice>
@endforeach
