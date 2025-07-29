<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suspect extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'suspect_id', 'part_no', 'lot_no', 'box_id', 'container_no', 'invoice_no', 'quantity', 'is_scanned', 'suspect_case_id', 'created_by', 'created_at', 'updated_by', 'updated_at', 'scanned_by', 'scanned_at', 'progress_quantity',
    ];
}
