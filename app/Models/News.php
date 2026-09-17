<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    protected $fillable = [
        'title',
        'content',
        'cover_image',
        'attachment',
        'category',
        'admin_id',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected $appends = [
        'cover_image_url',
        'attachment_url',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment ? asset('storage/' . $this->attachment) : null;
    }
}