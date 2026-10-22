<?php

namespace App\Enums;

enum RobotUnitStatus: string
{
    case Available = 'tersedia';
    case Borrowed = 'dipinjam';
    case Maintenance = 'perawatan';
    case Lost = 'hilang';
}
