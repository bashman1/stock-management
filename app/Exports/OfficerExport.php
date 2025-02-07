<?php

namespace App\Exports;

use App\Models\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class OfficerExport implements FromView
{

    protected array $reportData = [];
    public function __construct($data)
    {
        $this->reportData = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
//    public function collection()
//    {
//        return Collection::all();
//    }

    public function view():View
    {
        return view('collection.officerCollection', [
            'collection'=>$this->reportData
        ]);
    }
}
