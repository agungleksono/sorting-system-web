<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuspectCase extends Model
{
    use HasFactory;

    protected $primaryKey = 'suspect_case_id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    public $timestamps = false;

    protected $fillable = [
        'suspect_case_id', 'title', 'scan_parameter_code', 'scan_type_id', 'qr_length', 'string_start_index', 'string_length', 'is_closed', 'created_by', 'created_at', 'updated_by', 'updated_at',
    ];
}
