<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'title', 'content', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}
