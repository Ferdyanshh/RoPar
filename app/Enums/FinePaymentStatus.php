<?php

namespace App\Enums;

enum FinePaymentStatus: string
{
    case Pending = 'menunggu';
    case Paid = 'lunas';
    case Rejected = 'ditolak';
}
