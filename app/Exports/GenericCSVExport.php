<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class GenericCSVExport implements FromView
{
    protected array $genericData;

    public function __construct(array $genericData)
    {
        $this->genericData = $genericData;
    }

    public function view(): View
    {
        return view('generic.report', [
            'genericData' => $this->genericData
        ]);
    }
}
