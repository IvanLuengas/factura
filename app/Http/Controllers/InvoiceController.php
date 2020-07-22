<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\DataConfig;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        // consulta DB
        $invoice = new Invoice();
        $dataInvoice = $invoice->getInvoiceDesc();
        
        // data archivo config
        $config=DataConfig::getDataConfig();

        // TODO: agregar variables willi
        return view('invoice');
    }

}
