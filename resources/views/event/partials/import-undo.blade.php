{{-- "Undo this import": a quiet button and the dialog that confirms it. $count is how many events
     the last import added that are still there. Which events go is decided on the server, by the
     batch in the session; the form sends nothing but its token.

     A native <dialog>: it traps focus, closes on Escape and dims the page with no script of its
     own beyond opening and closing it. --}}
@php $undoId = 'import-undo-'.\Illuminate\Support\Str::random(6); @endphp
<button type="button" data-import-undo-open="{{ $undoId }}"
        class="rounded-md px-2 py-1 text-sm font-medium text-gray-600 underline-offset-2 transition-all duration-200 hover:text-gray-900 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] dark:text-gray-400 dark:hover:text-gray-100">
    {{ __('messages.import_panel_undo') }}
</button>

<dialog id="{{ $undoId }}" class="w-[calc(100%-2rem)] max-w-md rounded-xl bg-white p-0 text-gray-900 shadow-lg backdrop:bg-black/50 dark:bg-gray-800 dark:text-gray-100">
    <form method="POST" action="{{ route('event.import_undo', ['subdomain' => $role->subdomain]) }}" class="p-6">
        @csrf
        <p class="text-base">{{ __('messages.import_undo_confirm', ['count' => $count]) }}</p>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" data-import-undo-close="{{ $undoId }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-base font-semibold text-gray-900 shadow-sm transition-all duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-800">
                {{ __('messages.cancel') }}
            </button>
            <x-danger-button>{{ __('messages.import_undo_remove') }}</x-danger-button>
        </div>
    </form>
</dialog>

<script {!! nonce_attr() !!}>
    (function () {
        var dialog = document.getElementById(@json($undoId));
        var open = document.querySelector('[data-import-undo-open="' + @json($undoId) + '"]');
        var close = document.querySelector('[data-import-undo-close="' + @json($undoId) + '"]');
        if (! dialog || ! open || typeof dialog.showModal !== 'function') {
            return;
        }
        open.addEventListener('click', function () { dialog.showModal(); });
        close.addEventListener('click', function () { dialog.close(); });
    })();
</script>
