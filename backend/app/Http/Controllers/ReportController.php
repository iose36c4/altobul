<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reportable_type' => ['required', 'string', 'in:user,post,photo,message,conversation'],
            'reportable_id' => ['required', 'string'],
            'reason' => ['required', 'string', 'in:SPAM,ABUSE,HARASSMENT,INAPPROPRIATE,FAKE,UNDERAGE,OTHER'],
            'description' => ['sometimes', 'string', 'max:1000'],
        ]);

        $report = Report::create([
            'id' => Str::uuid()->toString(),
            'reporter_id' => $request->user()->id,
            'reportable_type' => $validated['reportable_type'],
            'reportable_id' => $validated['reportable_id'],
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'PENDING',
        ]);

        return response()->json([
            'message' => 'Report submitted successfully',
            'report' => $report,
        ], 201);
    }
}
