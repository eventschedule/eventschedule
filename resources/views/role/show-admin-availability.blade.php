<form method="post" id="availability_form"
        action="{{ route('role.availability', ['subdomain' => $subdomain]) }}">

        @csrf

    <input type="hidden" id="unavailable_days" name="unavailable_days"/>
    <input type="hidden" id="available_days" name="available_days"/>
    <input type="hidden" id="month" name="month" value="{{ $month }}"/>
    <input type="hidden" id="year" name="year" value="{{ $year }}"/>

</form>

@include('role/partials/calendar', ['route' => 'admin', 'tab' => 'availability'])