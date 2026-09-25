<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'role', 'created_at'])
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'users_page');

        $activities = ActivityLog::query()
            ->with('actor:id,name,email')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'activity_page');

        return response()->json([
            'stats' => [
                'users' => User::query()->count(),
                'admins' => User::query()->where('role', 'admin')->count(),
                'listings' => Listing::query()->count(),
                'activities' => ActivityLog::query()->count(),
            ],
            'users' => $users,
            'activities' => $activities,
        ]);
    }
}
