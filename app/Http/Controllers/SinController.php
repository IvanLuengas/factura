<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\DataConfig;

class SinController extends Controller
{
    public function index()
    {
        // verificar si existe archivo de configuracion
        $config=DataConfig::getDataExist();
        // consulta DB
        $invoice = new Invoice();
        $dataInvoice = array();
        if($config){
            $dataInvoice = $invoice->getInvoiceData();
        }
        return view('index', ['invoice' => $dataInvoice, 'config' =>$config]);
    }
}
