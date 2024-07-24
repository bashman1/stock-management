<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class SalesItemReport implements FromView
{

    protected array $reportData = [];
    public function __construct($data)
    {
        $this->reportData = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view() :View
    {
        return view('sales.salesItem', [
            'sales'=> $this->reportData
        ]);
    }
}
