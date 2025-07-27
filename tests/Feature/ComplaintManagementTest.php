<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Complaint;
use App\Models\ComplaintStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ComplaintManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'technician']);
        Role::create(['name' => 'user']);
        
        // Create complaint statuses - use firstOrCreate to avoid duplicates
        ComplaintStatus::firstOrCreate(
            ['slug' => 'open'],
            [
                'name' => 'Open',
                'color' => '#3B82F6',
                'sort_order' => 1,
                'is_active' => true
            ]
        );
        
        ComplaintStatus::firstOrCreate(
            ['slug' => 'in-progress'],
            [
                'name' => 'In Progress',
                'color' => '#F59E0B',
                'sort_order' => 2,
                'is_active' => true
            ]
        );
        
        ComplaintStatus::firstOrCreate(
            ['slug' => 'resolved'],
            [
                'name' => 'Resolved',
                'color' => '#10B981',
                'sort_order' => 3,
                'is_active' => true
            ]
        );
    }

    public function test_user_can_create_complaint(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/complaints', [
            'title' => 'Test Complaint',
            'description' => 'This is a test complaint description.',
            'priority' => 'medium',
            'category' => 'technical',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'complaint_number',
                        'title',
                        'description',
                        'priority',
                        'category'
                    ]
                ]);

        $this->assertDatabaseHas('complaints', [
            'title' => 'Test Complaint',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_view_own_complaints(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        
        $openStatus = ComplaintStatus::where('slug', 'open')->first();
        $complaint = Complaint::factory()->create([
            'user_id' => $user->id,
            'complaint_status_id' => $openStatus->id
        ]);
        
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/complaints/{$complaint->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $complaint->id,
                        'title' => $complaint->title,
                    ]
                ]);
    }

    public function test_user_cannot_view_others_complaints(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->assignRole('user');
        $user2->assignRole('user');
        
        $openStatus = ComplaintStatus::where('slug', 'open')->first();
        $complaint = Complaint::factory()->create([
            'user_id' => $user2->id,
            'complaint_status_id' => $openStatus->id
        ]);
        
        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/complaints/{$complaint->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_assign_complaint_to_technician(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $technician = User::factory()->create();
        $user = User::factory()->create();
        
        $admin->assignRole('admin');
        $technician->assignRole('technician');
        $user->assignRole('user');
        
        $openStatus = ComplaintStatus::where('slug', 'open')->first();
        $complaint = Complaint::factory()->create([
            'user_id' => $user->id,
            'complaint_status_id' => $openStatus->id
        ]);
        
        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/complaints/{$complaint->id}/assign", [
            'assigned_to' => $technician->id,
            'notes' => 'Assigning to technician for resolution'
        ]);

        // Add debugging if the response is not 200
        if ($response->getStatusCode() !== 200) {
            dump($response->getContent());
            dump($response->getStatusCode());
        }

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'assigned_to' => $technician->id,
        ]);
        
        $this->assertDatabaseHas('complaint_assignments', [
            'complaint_id' => $complaint->id,
            'assigned_to' => $technician->id,
            'assigned_by' => $admin->id,
        ]);
    }

    public function test_technician_can_resolve_assigned_complaint(): void
    {
        $technician = User::factory()->create();
        $user = User::factory()->create();
        
        $technician->assignRole('technician');
        $user->assignRole('user');
        
        $openStatus = ComplaintStatus::where('slug', 'open')->first();
        $complaint = Complaint::factory()->create([
            'user_id' => $user->id,
            'complaint_status_id' => $openStatus->id,
            'assigned_to' => $technician->id,
        ]);
        
        Sanctum::actingAs($technician);

        $response = $this->postJson("/api/complaints/{$complaint->id}/resolve", [
            'resolution_notes' => 'Issue has been fixed by updating the system.',
            'resolution_type' => 'resolved',
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('complaint_resolutions', [
            'complaint_id' => $complaint->id,
            'resolved_by' => $technician->id,
            'resolution_type' => 'resolved',
        ]);
        
        $complaint->refresh();
        $this->assertNotNull($complaint->resolved_at);
    }

    public function test_complaint_creation_with_file_upload(): void
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        $user->assignRole('user');
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('test-document.pdf', 100);

        $response = $this->postJson('/api/complaints', [
            'title' => 'Test Complaint with Attachment',
            'description' => 'This complaint includes an attachment.',
            'priority' => 'high',
            'category' => 'technical',
            'attachments' => [$file],
        ]);

        $response->assertStatus(201);
        
        $complaint = Complaint::latest()->first();
        $this->assertCount(1, $complaint->attachments);
        
        Storage::disk('public')->assertExists($complaint->attachments->first()->file_path);
    }

    public function test_dashboard_stats_for_different_roles(): void
    {
        $admin = User::factory()->create();
        $technician = User::factory()->create();
        $user = User::factory()->create();
        
        $admin->assignRole('admin');
        $technician->assignRole('technician');
        $user->assignRole('user');
        
        $openStatus = ComplaintStatus::where('slug', 'open')->first();
        
        // Create complaints
        Complaint::factory()->count(5)->create([
            'user_id' => $user->id,
            'complaint_status_id' => $openStatus->id
        ]);
        Complaint::factory()->count(3)->create([
            'user_id' => $user->id,
            'complaint_status_id' => $openStatus->id,
            'assigned_to' => $technician->id
        ]);
        
        // Test admin stats
        Sanctum::actingAs($admin);
        $response = $this->getJson('/api/dashboard/stats');
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total',
                        'open',
                        'resolved',
                        'high_priority',
                        'critical_priority'
                    ]
                ]);
        
        // Test technician stats
        Sanctum::actingAs($technician);
        $response = $this->getJson('/api/dashboard/stats');
        $response->assertStatus(200)
                ->assertJsonPath('data.assigned_to_me', 3);
        
        // Test user stats
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/dashboard/stats');
        $response->assertStatus(200)
                ->assertJsonPath('data.total', 8);
    }
}
