<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'SinController@index');

Route::get('invoice', 'InvoiceController@index');

Route::get('factura/enviarFactura', 'InvoiceController@sendBill');

Route::get('factura/obtenerStatus', 'InvoiceController@getStatus');

Route::get('factura/obtenerRango', 'InvoiceController@getRange');

Route::get('factura/obtenerStatusZip', array('uses' => 'InvoiceController@getStatusZip', 'as' => 'factura/obtenerStatusZip'));

Route::get('factura/firmarFactura', array('uses' => 'InvoiceController@getBill', 'as' => 'factura/firmarFactura'));

Route::get('factura/obtenerFactura', 'InvoiceController@getBillInfo');

