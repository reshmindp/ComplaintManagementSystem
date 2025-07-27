<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ComplaintStatus;

class ComplaintStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Open', 'slug' => 'open', 'color' => '#3B82F6', 'sort_order' => 1],
            ['name' => 'In Progress', 'slug' => 'in-progress', 'color' => '#F59E0B', 'sort_order' => 2],
            ['name' => 'Pending Customer', 'slug' => 'pending-customer', 'color' => '#8B5CF6', 'sort_order' => 3],
            ['name' => 'Resolved', 'slug' => 'resolved', 'color' => '#10B981', 'sort_order' => 4],
            ['name' => 'Closed', 'slug' => 'closed', 'color' => '#6B7280', 'sort_order' => 5],
        ];

        foreach ($statuses as $status) {
            ComplaintStatus::create($status);
        }
    }
}
