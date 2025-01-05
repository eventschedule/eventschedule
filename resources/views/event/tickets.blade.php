{{ $event->id }}

<form action="{{ route('event.checkout', ['subdomain' => $subdomain]) }}" method="post">
    @csrf
    
    <input type="hidden" name="event_id" value="{{ $event->id }}">

    <button type="submit" class="inline-flex gap-x-1.5 rounded-md bg-white px-6 py-3 text-lg font-semibold text-gray-500 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
        {{ __('messages.checkout') }}
    </button>

</form>