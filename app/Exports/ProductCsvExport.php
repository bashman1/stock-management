<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProductCsvExport implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {

        $products = Product::with(['stock', 'category', 'subCategory', 'productType', 'measurement'])->get();
        return view('product.reports', [
            'products' => $products
        ]);
    }
}
