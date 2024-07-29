<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function home(Request $request)
    {
        $events = [];
        $month = $request->month;
        $year = $request->year;
        $startOfMonth = '';
        $endOfMonth = '';

        if (! $month) {
            $month = now()->month;
        }
        if (! $year) {
            $year = now()->year;
        }

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $events = Event::with(['role'])
            //->whereVenueId($role->id)
            ->whereVisibility('public')
            ->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
            ->orderBy('starts_at')
            ->get();

        return view('home', compact(
            'events',
            'month',
            'year',
            'startOfMonth',
            'endOfMonth',
        ));
    }

    public function privacy() 
    {
        return view('privacy');
    }

    public function terms() 
    {
        return view('terms');
    }

    public function message(Request $request)
    {
        dd($request->all());
        // TODO implement me
    }
}