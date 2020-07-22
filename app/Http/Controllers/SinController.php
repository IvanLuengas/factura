<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;

class SinController extends Controller
{
    

    public function index()
    {
        // consulta DB
        $invoice = new Invoice();
        $dataInvoice = $invoice->getInvoiceData();
        return view('index', ['invoice' => $dataInvoice]);
    }
}
