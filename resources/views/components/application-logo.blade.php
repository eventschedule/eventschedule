<div class="p-6">
    <img class="h-10 md:h-12 w-auto dark:hidden" src="{{ asset(ltrim(config('app.logo_dark'), '/')) }}" alt="{{ config('app.name') }}"/>
    <img class="h-10 md:h-12 w-auto hidden dark:block" src="{{ asset(ltrim(config('app.logo_light'), '/')) }}" alt="{{ config('app.name') }}"/>
</div>
