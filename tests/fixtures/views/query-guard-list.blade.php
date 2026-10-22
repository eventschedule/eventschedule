{{-- QueryGuardTest: a list that reads each row's relation, so the finding is named by this file. --}}
@foreach ($events as $event)
<p>{{ $event->creatorRole?->name }}</p>
@endforeach
