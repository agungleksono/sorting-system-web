<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanParameter extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'code', 'scan_parameter', 'created_by', 'created_at', 'updated_by', 'updated_at',
    ];
}
