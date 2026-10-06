<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectEntry extends Model
{
    use HasFactory;

    public const TYPES = ['location' => 'स्थान / Location', 'topic' => 'विषय / Topic', 'section' => 'खण्ड / Section'];

    protected $fillable = ['book_page', 'root_member_id', 'title', 'type', 'location', 'description', 'additional_notes', 'parent_id', 'display_order', 'status'];

    protected function casts(): array
    {
        return ['book_page' => 'integer', 'root_member_id' => 'integer', 'status' => 'boolean', 'display_order' => 'integer'];
    }

    public function publicUrl(): string
    {
        return route($this->type === 'location' ? 'places.show' : 'subjects.show', $this);
    }

    public function rootMember(): BelongsTo
    {
        return $this->belongsTo(BansawaliMember::class, 'root_member_id');
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
