<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintStatusResource;
use App\Models\ComplaintStatus;
use Illuminate\Http\JsonResponse;

class ComplaintStatusController extends Controller
{
    public function index(): JsonResponse
    {
        $statuses = ComplaintStatus::active()
            ->ordered()
            ->withCount('complaints')
            ->get();

        return response()->json([
            'data' => ComplaintStatusResource::collection($statuses)
        ]);
    }
}
