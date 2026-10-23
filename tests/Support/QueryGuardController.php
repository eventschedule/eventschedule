<?php

namespace Tests\Support;

use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFactory;

/**
 * QueryGuardTest's controller: a view that reads each row's relation, returned from a controller
 * (Laravel renders it after the controller has returned, so the finding's entry must come from
 * the route, not from the stack).
 */
final class QueryGuardController
{
    public function __invoke(): View
    {
        ViewFactory::addLocation(base_path('tests/fixtures/views'));

        return view('query-guard-list', ['events' => Event::query()->get()]);
    }
}
