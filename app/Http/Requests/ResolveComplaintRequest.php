<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolveComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('resolve', $this->route('complaint'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resolution_notes' => ['required', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:2000'],
            'resolution_type' => ['required', 'in:resolved,closed,escalated,duplicate'],
        ];
    }
}
