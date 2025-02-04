<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suspect extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'suspect_id', 'part_no', 'lot_no', 'invoice_id'
    ];
}
