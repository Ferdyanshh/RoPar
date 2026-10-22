<?php

namespace App\Models;

use Database\Factories\RobotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'model', 'description', 'photo'])]
class Robot extends Model
{
    /** @use HasFactory<RobotFactory> */
    use HasFactory;

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'robot_category')->withTimestamps();
    }

    public function units(): HasMany
    {
        return $this->hasMany(RobotUnit::class);
    }
}
