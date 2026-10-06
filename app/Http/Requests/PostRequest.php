<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'slug' => ['required', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'max:255', Rule::unique('blog_posts', 'slug')->ignore($this->route('post'))], 'short_description' => ['nullable', 'string', 'max:1000'], 'content' => ['required', 'string', 'max:200000'], 'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:4096'], 'remove_image' => ['nullable', 'boolean'], 'status' => ['required', Rule::in(['draft', 'published'])], 'published_at' => ['nullable', 'date', 'required_if:status,published'], 'meta_title' => ['nullable', 'string', 'max:255'], 'meta_description' => ['nullable', 'string', 'max:500']];
    }
}
