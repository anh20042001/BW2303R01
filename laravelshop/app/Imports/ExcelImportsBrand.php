<?php

namespace App\Imports;

use App\Models\BrandProductModel;
use Maatwebsite\Excel\Concerns\ToModel;

class ExcelImportsBrand implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new BrandProductModel([
            'brand_name' => $row[0],
            'brand_desc' => $row[1],
            'brand_status' => $row[2],
        ]);
    }
}
