<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return ['root_generation' => ['nullable', 'integer', 'min:1', 'max:1000000'], 'location_names' => ['nullable', 'string', 'max:2000'], 'annotation' => ['nullable', 'string', 'max:255'], 'name_ne' => ['required', 'string', 'max:255'], 'name_en' => ['nullable', 'string', 'max:255'], 'parent_id' => ['nullable', 'integer', 'exists:bansawali_members,id'], 'short_description' => ['nullable', 'string', 'max:2000'], 'display_order' => ['required', 'integer', 'min:0', 'max:1000000'], 'status' => ['required', 'boolean']];
    }
}
