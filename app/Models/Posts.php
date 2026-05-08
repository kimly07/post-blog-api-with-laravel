<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// #[Fillable(['title', 'body', 'image_url', 'user_id'])]

class Posts extends Model
{
    protected $fillable = ['title', 'body', 'image_url', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function reactions()
    {
        return $this->hasMany(PostReaction::class, 'post_id');
    }
}
