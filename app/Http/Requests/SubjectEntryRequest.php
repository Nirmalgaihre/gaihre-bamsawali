<?php

namespace App\Http\Requests;

use App\Models\SubjectEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return ['book_page' => ['nullable', 'integer', 'min:1', 'max:1000000'], 'root_member_id' => ['nullable', 'integer', 'exists:bansawali_members,id'], 'title' => ['required', 'string', 'max:255'], 'type' => ['required', Rule::in(array_keys(SubjectEntry::TYPES))],
            'location' => ['nullable', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:10000'],
            'additional_notes' => ['nullable', 'string', 'max:5000'], 'parent_id' => ['nullable', 'integer', 'exists:subject_entries,id'],
            'display_order' => ['required', 'integer', 'min:0', 'max:1000000'], 'status' => ['required', 'boolean']];
    }
}
