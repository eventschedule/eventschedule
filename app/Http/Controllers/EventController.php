<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function create()
    {
        $event = new Event;

        $data = [
            'event' => $event,
        ];

        return view('event/edit', $data);
    }

}