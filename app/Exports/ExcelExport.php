<?php

namespace App\Exports;

// use App\Excel;

use Illuminate\Contracts\View\View;

use Maatwebsite\Excel\Concerns\Exportable;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromView;


class ExcelExport implements FromView
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($getData = array(),$searchDataForView = array(),$viewName = "")
    {
        $this->getData = $getData;
        $this->searchDataForView = $searchDataForView;
        $this->viewName = $viewName;
    }

    /*public function query()
    {
        // return $this->getData->getQuery();
    }*/

    
    public function view(): View
    {
    	
        return view($this->viewName, [
            'dataForView' => $this->getData->get(),
            'searchDataForView'=>$this->searchDataForView
        ]);
    }
    
}
