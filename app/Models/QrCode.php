<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'qr_id', 'qr_content', 'judgment', 'part_no', 'lot_no', 'invoice_no', 'created_by', 'created_at'
    ];
}
