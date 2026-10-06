<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'content' => ['required', 'string', 'max:200000'], 'status' => ['required', 'boolean']];
    }
}
