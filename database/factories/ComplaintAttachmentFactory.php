<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComplaintAttachmentFactory extends Factory
{
    public function definition(): array
    {
        $fileName = fake()->word() . '.pdf';
        
        return [
            'complaint_id' => Complaint::factory(),
            'uploaded_by' => User::factory(),
            'original_name' => $fileName,
            'file_name' => time() . '_' . $fileName,
            'file_path' => 'complaints/1/' . $fileName, 
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(1000, 1000000),
        ];
    }
}
