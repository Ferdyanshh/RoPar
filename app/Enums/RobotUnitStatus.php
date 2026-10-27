<?php

namespace App\Enums;

enum RobotUnitStatus: string
{
    case Available = 'tersedia';
    case Borrowed = 'dipinjam';
    case Damaged = 'rusak';
}
