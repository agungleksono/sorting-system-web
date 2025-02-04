<?php

namespace App\Imports;

use App\Models\Suspect;
use Maatwebsite\Excel\Concerns\ToModel;

class SuspectsImport implements ToModel
{
    public function model(array $row)
    {
        return new Suspect([
            'part_no' => $row[0],
            'lot_no' => $row[1],
            'invoice_id' => $row[3],
        ]);
    }
}