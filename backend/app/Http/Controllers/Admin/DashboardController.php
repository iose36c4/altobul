<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Friendship;
use App\Models\GeoZone;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Toke;
use App\Models\User;
use App\Models\UserMatch;
use App\Models\VerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function metrics(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $metrics = [
            'users' => [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'new_24h' => User::where('created_at', '>', now()->subDay())->count(),
                'new_7d' => User::where('created_at', '>', now()->subWeek())->count(),
                'by_role' => User::select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->get()
                    ->mapWithKeys(fn ($r) => [$r->role => (int) $r->count])
                    ->toArray(),
                'by_verification' => User::select('verification_status', DB::raw('count(*) as count'))
                    ->groupBy('verification_status')
                    ->get()
                    ->mapWithKeys(fn ($r) => [$r->verification_status => (int) $r->count])
                    ->toArray(),
            ],
            'matches' => [
                'active' => UserMatch::where('status', 'active')
                    ->where('expires_at', '>', now())
                    ->count(),
            ],
            'friendships' => [
                'active' => Friendship::where('status', 'active')->count(),
            ],
            'tokes' => [
                'active' => Toke::where('status', 'active')
                    ->where('expires_at', '>', now())
                    ->count(),
            ],
            'posts' => [
                'active' => Post::where('status', 'active')
                    ->where('expires_at', '>', now())
                    ->count(),
            ],
            'photos' => [
                'active' => Photo::where('status', 'active')->count(),
            ],
            'verifications' => [
                'pending' => VerificationRequest::where('status', 'pending')->count(),
                'approved_today' => VerificationRequest::where('status', 'approved')
                    ->whereDate('reviewed_at', today())
                    ->count(),
                'rejected_today' => VerificationRequest::where('status', 'rejected')
                    ->whereDate('reviewed_at', today())
                    ->count(),
            ],
            'geo_zones' => [
                'active' => GeoZone::where('is_active', true)->count(),
            ],
            'api_keys' => [
                'active' => ApiKey::whereNull('revoked_at')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->count(),
            ],
        ];

        return response()->json(['metrics' => $metrics]);
    }

    public function charts(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $days = 30;
        $startDate = now()->subDays($days - 1)->startOfDay();

        $userRegistrations = User::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $matchesCreated = UserMatch::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $tokesSent = Toke::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $tokesConsumed = Toke::selectRaw('DATE(consumed_at) as date, count(*) as count')
            ->where('consumed_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $postsCreated = Post::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $verificationsRequested = VerificationRequest::selectRaw('DATE(submitted_at) as date, count(*) as count')
            ->where('submitted_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $verificationsApproved = VerificationRequest::selectRaw('DATE(reviewed_at) as date, count(*) as count')
            ->where('reviewed_at', '>=', $startDate)
            ->where('status', 'approved')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $verificationsRejected = VerificationRequest::selectRaw('DATE(reviewed_at) as date, count(*) as count')
            ->where('reviewed_at', '>=', $startDate)
            ->where('status', 'rejected')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $usersData = [];
        $matchesData = [];
        $tokesSentData = [];
        $tokesConsumedData = [];
        $postsData = [];
        $verifRequestedData = [];
        $verifApprovedData = [];
        $verifRejectedData = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $labels[] = $date;
            $usersData[] = $userRegistrations[$date]->count ?? 0;
            $matchesData[] = $matchesCreated[$date]->count ?? 0;
            $tokesSentData[] = $tokesSent[$date]->count ?? 0;
            $tokesConsumedData[] = $tokesConsumed[$date]->count ?? 0;
            $postsData[] = $postsCreated[$date]->count ?? 0;
            $verifRequestedData[] = $verificationsRequested[$date]->count ?? 0;
            $verifApprovedData[] = $verificationsApproved[$date]->count ?? 0;
            $verifRejectedData[] = $verificationsRejected[$date]->count ?? 0;
        }

        return response()->json([
            'charts' => [
                'users' => [
                    'labels' => $labels,
                    'data' => $usersData,
                ],
                'activity' => [
                    'labels' => $labels,
                    'matches' => $matchesData,
                    'tokes' => $tokesSentData,
                    'tokes_consumed' => $tokesConsumedData,
                    'posts' => $postsData,
                ],
                'verifications' => [
                    'labels' => $labels,
                    'requested' => $verifRequestedData,
                    'approved' => $verifApprovedData,
                    'rejected' => $verifRejectedData,
                ],
            ],
        ]);
    }

    private function authorizeAdmin(?User $user): void
    {
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Administrative privileges required');
        }
    }
}
