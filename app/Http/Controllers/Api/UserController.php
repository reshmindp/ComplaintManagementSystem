<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): UserCollection
    {
        $query = User::with('roles:id,name')
            ->select(['id', 'name', 'email', 'phone', 'is_active', 'created_at']);

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->latest()->paginate($request->input('per_page', 15));

        return new UserCollection($users);
    }

    public function show(User $user): UserResource
    {
        $user->load(['roles:id,name', 'complaints:id,title,complaint_number,created_at']);
        return new UserResource($user);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->filled('roles')) {
            $user->assignRole($request->roles);
        }

        return response()->json([
            'message' => 'User created successfully',
            'data' => new UserResource($user->load('roles'))
        ], 201);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        if ($request->filled('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'data' => new UserResource($user->refresh()->load('roles'))
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    public function technicians(): JsonResponse
    {
        $technicians = User::role(['technician', 'admin'])
            ->active()
            ->select(['id', 'name', 'email'])
            ->get();

        return response()->json([
            'data' => UserResource::collection($technicians)
        ]);
    }

    public function toggleStatus(User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'message' => $user->is_active ? 'User activated successfully' : 'User deactivated successfully',
            'data' => new UserResource($user)
        ]);
    }
}
