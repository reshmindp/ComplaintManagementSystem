<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintRequest;
use App\Http\Requests\AssignComplaintRequest;
use App\Http\Requests\ResolveComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Http\Resources\ComplaintCollection;
use App\Models\Complaint;
use App\Services\ComplaintService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ComplaintController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private ComplaintService $complaintService)
    {
        $this->authorizeResource(Complaint::class, 'complaint');
    }

    public function index(Request $request): ComplaintCollection
    {
        $filters = $request->only([
            'status', 'priority', 'category', 'assigned_to', 
            'unassigned', 'search'
        ]);

        $complaints = $this->complaintService->getComplaints(
            $filters, 
            $request->input('per_page', 15)
        );

        return new ComplaintCollection($complaints);
    }

    public function show(Complaint $complaint): ComplaintResource
    {
        $complaint->load([
            'user:id,name,email',
            'status',
            'assignedUser:id,name',
            'assignments.assignedBy:id,name',
            'assignments.assignedTo:id,name',
            'resolution.resolvedBy:id,name',
            'attachments'
        ]);

        return new ComplaintResource($complaint);
    }

    public function store(StoreComplaintRequest $request): JsonResponse
    {
        $complaint = $this->complaintService->createComplaint(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Complaint created successfully',
            'data' => new ComplaintResource($complaint)
        ], 201);
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint): JsonResponse
    {
        $complaint->update($request->validated());

        return response()->json([
            'message' => 'Complaint updated successfully',
            'data' => new ComplaintResource($complaint->refresh())
        ]);
    }

    public function destroy(Complaint $complaint): JsonResponse
    {
        $complaint->delete();

        return response()->json([
            'message' => 'Complaint deleted successfully'
        ]);
    }

    public function assign(AssignComplaintRequest $request, Complaint $complaint): JsonResponse
    {
        try {
            $this->complaintService->assignComplaint(
                $complaint,
                $request->input('assigned_to'),
                $request->user(),
                $request->input('notes')
            );

            return response()->json([
                'message' => 'Complaint assigned successfully',
                'data' => new ComplaintResource($complaint->refresh()->load([
                    'user:id,name,email',
                    'status',
                    'assignedUser:id,name',
                    'assignments.assignedBy:id,name',
                    'assignments.assignedTo:id,name'
                ]))
            ]);
        } catch (\Exception $e) {
            \Log::error('Error assigning complaint: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to assign complaint',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resolve(ResolveComplaintRequest $request, Complaint $complaint): JsonResponse
    {
        $this->complaintService->resolveComplaint(
            $complaint,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Complaint resolved successfully',
            'data' => new ComplaintResource($complaint->refresh())
        ]);
    }
}
