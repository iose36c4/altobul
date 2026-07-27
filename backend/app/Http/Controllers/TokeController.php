<?php

namespace App\Http\Controllers;

use App\Http\Resources\TokeResource;
use App\Models\Toke;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TokeController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'receiver_id' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $receiver = User::find($data['receiver_id']);

        $this->authz->canSendToke($user, $receiver)->throwIfDenied();

        $toke = Toke::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiver->id,
            'expires_at' => now()->addHours(48),
            'status' => 'ACTIVE',
        ]);

        return response()->json([
            'toke' => new TokeResource($toke->load(['sender', 'receiver'])),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $sent = Toke::where('sender_id', $user->id)
            ->where('status', 'ACTIVE')
            ->where('expires_at', '>', now())
            ->with('receiver.profile')
            ->latest()
            ->paginate(20);

        $received = Toke::where('receiver_id', $user->id)
            ->where('status', 'ACTIVE')
            ->where('expires_at', '>', now())
            ->with('sender.profile')
            ->latest()
            ->paginate(20);

        return response()->json([
            'sent' => TokeResource::collection($sent)->response()->getData(true),
            'received' => TokeResource::collection($received)->response()->getData(true),
        ]);
    }

    public function consume(Toke $toke): JsonResponse
    {
        $user = request()->user();

        if ($toke->receiver_id !== $user->id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You are not the receiver of this toke',
            ], 403);
        }

        if ($toke->status !== 'ACTIVE' || $toke->isExpired()) {
            return response()->json([
                'error' => 'Invalid toke',
                'message' => 'This toke is no longer active',
            ], 422);
        }

        $result = DB::transaction(function () use ($user, $toke) {
            $locked = Toke::where('id', $toke->id)
                ->lockForUpdate()
                ->first();

            if (! $locked || $locked->status !== 'ACTIVE' || $locked->isExpired()) {
                return null;
            }

            $existingMutual = Toke::where('sender_id', $user->id)
                ->where('receiver_id', $toke->sender_id)
                ->where('status', 'ACTIVE')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->exists();

            $locked->update([
                'status' => 'CONSUMED',
                'matched_at' => now(),
            ]);

            if (! UserMatch::between($user, $toke->sender_id)->active()->lockForUpdate()->exists()) {
                UserMatch::create([
                    'user_a_id' => min($user->id, $toke->sender_id),
                    'user_b_id' => max($user->id, $toke->sender_id),
                    'expires_at' => now()->addDays(7),
                    'status' => 'ACTIVE',
                ]);
            }

            return ['mutual_toke' => $existingMutual];
        });

        if (is_null($result)) {
            return response()->json([
                'error' => 'Invalid toke',
                'message' => 'This toke is no longer active',
            ], 422);
        }

        $toke->refresh();

        return response()->json([
            'toke' => new TokeResource($toke),
            'match_created' => true,
            'mutual_toke' => $result['mutual_toke'],
        ]);
    }

    public function cancel(Toke $toke): JsonResponse
    {
        $user = request()->user();

        if ($toke->sender_id !== $user->id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You can only cancel your own tokes',
            ], 403);
        }

        if ($toke->status !== 'ACTIVE') {
            return response()->json([
                'error' => 'Invalid toke',
                'message' => 'This toke is no longer active',
            ], 422);
        }

        $toke->update(['status' => 'CANCELLED']);

        return response()->json([
            'toke' => new TokeResource($toke->fresh()),
        ]);
    }
}
