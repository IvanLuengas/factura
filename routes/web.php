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

Route::get('factura/obtenerStatusZip', array('uses' => 'InvoiceController@getStatusZip', 'as' => 'factura/obtenerStatusZip'));

Route::get('factura/firmarFactura', array('uses' => 'InvoiceController@getBill', 'as' => 'factura/firmarFactura'));

Route::get('factura/obtenerFactura', function(){
	$notaria = "Notaria unica Granada";
	$CompanyName = "Marina Quintero Hoyos";
	$NITR = '217775754';
	$CompanyAddress = "Carrera Hoyos # 20 - 44";
	$telefono = '38320918';
	$EmailR = "unicagranadaantioquia@supernotariado.gov.co";
	$regimen = "Regimen Comun";
	$ID = "N2004-7501";
    // datos del receptor
    $CustomerName = "Arango Caballos Omar";
    $CustomerNit = '71603486';
    $CustomerEmail = '71603486';
	$d = new DateTime();
	$IssueDate = $d->format(DateTime::RFC3339_EXTENDED);
 	$publicDeed = '2004-96';
 	$publicDocument = "COMPRAVENTA";
    // valor neto
    $LineExtensionAmount = '1763000';
    // valor taxeabel
    $TaxExclusiveAmount = '56161';
    // total factura
    $PayableAmount = $LineExtensionAmount + $TaxExclusiveAmount;
    // otros impuestos, este codigo no esta diseñado para reportar Ipo Consumo o otros impuestos
    $codImp1 = "01 Derechos notariales";
    $ValImp1 =  '15.935.00';
    $codImp2 = '02 Copias originales';
    $ValImp2 =  '3.120.00';
    $codImp3 = '03 Copias protocolo';
    $ValImp3 =  '9.360.00';
	//Crear impuestos
    $codImp4 = '04 IVA';
    $ValImp4 =  '4.546.00';
    $codImp5 = '05 Retención en la fuente';
    $ValImp5 =  '17.630.00';    
    $codImp6 = '06 Recaudo fondo especial notariado';
    $ValImp6 =  '2.875.00';
    $codImp7 = '07 Recaudo Superintendencia de Notariado y Registro';
    $ValImp7 =  '2.785.00';
	return view('factura', compact(	'notaria','CompanyName','NITR','CompanyAddress','telefono','EmailR','regimen','ID','CustomerName','CustomerNit','CustomerEmail','IssueDate','publicDeed','publicDocument','LineExtensionAmount','TaxExclusiveAmount','PayableAmount','codImp1','ValImp1','codImp2','ValImp2','codImp3','ValImp3','codImp4','ValImp4','codImp5','ValImp5','codImp6','ValImp6','codImp7','ValImp7'));
})->name('factura');

