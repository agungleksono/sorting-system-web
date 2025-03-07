<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanType extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'scan_type_id', 'title', 'created_by', 'created_at',
    ];
}
