<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Complaint;
use App\Models\ComplaintStatus;
use App\Services\ComplaintService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ComplaintServiceTest extends TestCase
{
    use RefreshDatabase;

    private ComplaintService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ComplaintService();
        
        Role::create(['name' => 'user']);
        ComplaintStatus::create([
            'name' => 'Open',
            'slug' => 'open',
            'sort_order' => 1
        ]);
    }

    public function test_complaint_number_generation(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $complaint1 = $this->service->createComplaint([
            'title' => 'First Complaint',
            'description' => 'Description',
            'priority' => 'medium',
            'category' => 'technical',
        ], $user);

        $complaint2 = $this->service->createComplaint([
            'title' => 'Second Complaint',
            'description' => 'Description',
            'priority' => 'medium',
            'category' => 'technical',
        ], $user);

        $this->assertNotEquals($complaint1->complaint_number, $complaint2->complaint_number);
        $this->assertStringStartsWith('CMP' . date('Ym'), $complaint1->complaint_number);
        $this->assertStringStartsWith('CMP' . date('Ym'), $complaint2->complaint_number);
    }

    public function test_filtering_complaints(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        Complaint::factory()->create(['priority' => 'high', 'user_id' => $user->id]);
        Complaint::factory()->create(['priority' => 'low', 'user_id' => $user->id]);
        Complaint::factory()->create(['priority' => 'high', 'user_id' => $user->id]);

        $highPriorityComplaints = $this->service->getComplaints(['priority' => 'high']);
        
        $this->assertEquals(2, $highPriorityComplaints->total());
    }
}
