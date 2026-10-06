<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BansawaliMember extends Model
{
    use HasFactory;

    protected $fillable = ['root_generation', 'location_names', 'annotation', 'name_ne', 'name_en', 'parent_id', 'generation', 'short_description', 'display_order', 'status'];

    protected function casts(): array
    {
        return ['root_generation' => 'integer', 'status' => 'boolean', 'generation' => 'integer'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
