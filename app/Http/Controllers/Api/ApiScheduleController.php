<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class ApiScheduleController extends Controller
{
    protected const MAX_PER_PAGE = 1000;
    protected const DEFAULT_PER_PAGE = 100;

    public function index(Request $request)
    {
        $perPage = min(
            (int) $request->input('per_page', self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );

        $schedules = Role::where('user_id', auth()->id());
        
        if ($request->has('name')) {
            $schedules->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('type')) {
            $schedules->where('type', $request->type);
        }

        $schedules = $schedules->paginate($perPage);

        return response()->json([
            'data' => collect($schedules->items())->map(function($schedule) {
                return $schedule->toApiData();
            })->values(),
            'meta' => [
                'current_page' => $schedules->currentPage(),
                'from' => $schedules->firstItem(),
                'last_page' => $schedules->lastPage(),
                'per_page' => $schedules->perPage(),
                'to' => $schedules->lastItem(),
                'total' => $schedules->total(),
                'path' => $request->url(),
            ]
        ], 200, [], JSON_PRETTY_PRINT);
    }    
} 