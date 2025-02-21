<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintQueue extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'id', 'part_no', 'lot_no', 'invoice_no', 'judgment', 'status', 'created_by', 'created_at',
    ];
}
