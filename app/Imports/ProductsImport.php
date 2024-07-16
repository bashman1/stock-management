<?php

namespace App\Imports;

use App\Models\TempProduct;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel,WithHeadingRow
{
    protected $batchId;
    public function __construct(int $batchId)
    {
        $this->batchId=$batchId;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new TempProduct([
            //
        ]);
    }
}
