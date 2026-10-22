<?php

namespace App\Models;

use App\Enums\RobotUnitStatus;
use Database\Factories\RobotUnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['robot_id', 'unit_code', 'status', 'condition_note'])]
class RobotUnit extends Model
{
    /** @use HasFactory<RobotUnitFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['status' => RobotUnitStatus::class];
    }

    public function robot(): BelongsTo
    {
        return $this->belongsTo(Robot::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
