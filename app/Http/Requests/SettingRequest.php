<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return ['hero_title' => ['required', 'string', 'max:100'], 'hero_accent' => ['required', 'string', 'max:100'], 'site_name' => ['required', 'string', 'max:100'], 'site_name_ne' => ['required', 'string', 'max:100'], 'tagline' => ['required', 'string', 'max:255'], 'footer_text' => ['required', 'string', 'max:1000'], 'contact_text' => ['required', 'string', 'max:5000']];
    }
}
