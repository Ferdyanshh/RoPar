<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Borrowed = 'dipinjam';
    case Returned = 'dikembalikan';
    case Cancelled = 'dibatalkan';
}
