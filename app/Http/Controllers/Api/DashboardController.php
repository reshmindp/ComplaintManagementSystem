<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ComplaintService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private ComplaintService $complaintService)
    {
    
    }

    public function stats(Request $request): JsonResponse
    {
        $stats = $this->complaintService->getDashboardStats($request->user());

        return response()->json([
            'data' => $stats
        ]);
    }
}
