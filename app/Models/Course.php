<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'category', 'level', 'course_price',
        'feature_video', 'feature_images', 'status'
    ];

    protected $casts = [
        'feature_images' => 'array',
        'course_price' => 'decimal:2',
    ];

    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}
