<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suspect extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'suspect_id', 'part_no', 'lot_no', 'invoice_no', 'container_no', 'added_by', 'added_at', 'updated_by', 'updated_at',
    ];
}
