<form method="post" id="availability_form"
        action="{{ route('role.availability', ['subdomain' => $subdomain]) }}">

        @csrf

    <input type="text" id="unavailable_days" name="unavailable_days"/>
    <input type="text" id="available_days" name="available_days"/>

</form>

@include('role/partials/calendar', ['route' => 'admin', 'tab' => 'availability'])