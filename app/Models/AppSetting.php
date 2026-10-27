<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['borrowed_robot_limit', 'reservation_robot_limit'])]
class AppSetting extends Model
{
    public static function current(): self
    {
        return self::query()->findOrFail(1);
    }

    protected function casts(): array
    {
        return [
            'borrowed_robot_limit' => 'integer',
            'reservation_robot_limit' => 'integer',
        ];
    }
}
