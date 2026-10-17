<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    // Tambahkan baris $fillable ini:
    protected $fillable = [
        'title',
        'description',
        'link',
        'image',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];
}