<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\DataConfig;
use App\Models\FirmadorV2;
use App\Models\SignSoap;
use Carbon\Carbon;
use DOMDocument;
use ZipArchive;
use App\Models\SignWebService;

class InvoiceController extends Controller
{
    public function index()
    {
        // 
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendBill()
    {   

        $invoiceModel = new Invoice();

        // mock base : quitar 
        $invoice = (object) array(
            'fecfactur' => '2004-06-05 00:00:00',
            'tidresp' => 'CC',
            'invoice_number'=> '10',
        );
        // consulta DB        
        // $invoice = $invoiceModel->getInvoiceDesc();
        // // quitar
        // $invoice = $invoice[0];
        // consulta FIN DB

        // data archivo config
        $config=DataConfig::getDataConfig();
        $items = array();
        $user = null;
        $consecutive = 1;
        $office = null;
        $InvoiceAuthorization = null;
        $invoice->fecfactur  = Carbon::parse($invoice->fecfactur);        
        $InvoiceAuthorization = $config['InvoiceAuthorization'];
        $StartDate = $config['StartDate'];
        $EndDate = $config['EndDate'];
        $Prefix = $config['Prefix'];
        $From = $config['From'];
        $To =  $config['To'];
        $companyNIT = $config['companyNIT'];
        $companyNITDV = null;
        $SoftwareID = $config['SoftwareID'];
        $ClTec = $config['ClTec'];
        $pin = $config['pin'];
        $AuthorizationProviderID = $config['companyNIT'];
        $CustomizationID = env('CUSTOMIZATION_ID');
        $ProfileExecutionID = env('PROFILE_EXECUTION_ID');
        $ID = $Prefix.$invoice->invoice_number;
        $SoftwareSecurityCode = hash('sha384', $SoftwareID.$pin );  
        $IssueDate  = $invoice->fecfactur->format('Y-m-d');
        $IssueTime = $invoice->fecfactur->format('h:s:i')."-05:00";
        $InvoiceTypeCode = env('INVOICE_TYPE_CODE');
        $LineCountNumeric = '2';
        // $LineCountNumeric = $items->count(); // TODO: numero de productos?
        $InvoicePeriodStartDate = $invoice->fecfactur->startOfMonth()->toDateString(); 
        $InvoicePeriodEndDate =  $invoice->fecfactur->endOfMonth()->toDateString();
        $IndustryClasificationCode = $config['IndustryClasificationCode'];
        $CompanyName = 'GRUPO FAMILIA S.A.S';
        $CompanyAddress = 'Carrera 4 # 76 - 98';
        $CompanyCity = 'Medellín';
        $CompanyDepto = 'Antioquia';
        $CompanyDeptoCode = '05';
        $companyEmail = null;
        $CompanyPostCode = '193558';
        $TaxLevelCode = ' O-13;O-15';
        $TLClistName = '48';
        $cityCode = '05001';
        $TaxSchemeId = '01';
        $TaxSchemeName = 'IVA';
        $AdditionalAccountID = '1';
        // datos del receptor
        $CustomerName = null;
        $CustomerCity = null;
        $CustomerDepto = null;
        $CustomerAddress = null;
        $CustomerNit = '71603486';
        $CustomerEmail = '71603486';
        // codigo ciudad
        $CustomerCityCode = '05042';
        $CustomerDeptoCode = '05';
        // ver tabla, 31 para nit, 13 para cedula
        $CustomerIdCode = '31';
        // ver tabla 2 para persona natural, 1 para persona juridica
        $AdditionalAccountID = '2';
        // digito de verificacion nit cliente, null por defecto 
        $CustomerDV = null;
        // hay q informarlo si se identifica con nit
        if ($CustomerIdCode == '31') {
            $CustomerDV = $invoiceModel->getDV($CustomerNit);
            $AdditionalAccountID = '1';
        };
        // metodo de pago, ver tabla
        $PaymentMeansID = '1';
        $PaymentMeansCode = '10';
        // totales
        // valor taxeable
        $TaxableAmount = null;
        // total impuestos
        $TaxAmount = null;
        // procentaje de impuesto
        $Percent = '19';
        // valor neto
        $LineExtensionAmount = null;
        // valor taxeabel
        $TaxExclusiveAmount = null;
        // valor con impuestos
        $TaxInclusiveAmount = null;
        // otros cargos
        $ChargeTotalAmount = null;
        // total factura
        $PayableAmount = null;
        // otros impuestos, este codigo no esta diseñado para reportar Ipo Consumo o otros impuestos
        $codImp1 = '01';
        $ValImp1 =  $TaxAmount;
        $codImp2 = '04';
        $ValImp2 =  '0.00';
        $codImp3 = '03';
        $ValImp3 =  '0.00';
        $OtherTaxes = '0.00';
        // numero de productos en factura
        $LineCountNumeric = null;
        $codImp1 = '01';
        // cufe
        $cufe = $ID.$IssueDate.$IssueTime.$LineExtensionAmount.$codImp1.$ValImp1.$codImp2.$ValImp2.$codImp3.$ValImp3.$PayableAmount.$companyNIT.$CustomerNit.$ClTec.$ProfileExecutionID;
        $UUID = hash('sha384',$cufe);
        $QRCode = "NumFac: $ID FecFac: $IssueDate HorFac: $IssueTime NitFac: $companyNIT DocAdq: $CustomerNit ValFac: $LineExtensionAmount ValIva: $TaxAmount ValOtroIm: $OtherTaxes ValTolFac: $PayableAmount CUFE: $UUID https://catalogovpfe.dian.gov.co/document/searchqr?documentkey=$UUID";
        $xmlHead = $invoiceModel->formHeadXMl();
        $xmlExtensions = $invoiceModel->formExtensionXMl($InvoiceAuthorization,$StartDate,$EndDate,$Prefix,$From,$To,$companyNIT,$SoftwareID,$AuthorizationProviderID,$QRCode,$companyNITDV,$SoftwareSecurityCode);
        $xmlVersion = $invoiceModel->formVersionXMl($CustomizationID,$ProfileExecutionID,$ID,$UUID,$IssueDate,$IssueTime,$InvoiceTypeCode,$LineCountNumeric,$InvoicePeriodStartDate,$InvoicePeriodEndDate); 
        $xmlCompany = $invoiceModel->formCompanyXMl($CompanyName,$CompanyCity,$CompanyDepto,$CompanyDeptoCode,$CompanyAddress,$companyNIT,$TaxLevelCode,$cityCode,$TaxSchemeId,$TaxSchemeName,$companyNITDV,$TLClistName,$companyEmail,$Prefix);
        $xmlCustomer = $invoiceModel->formCustomerXMl($AdditionalAccountID,$CustomerName,$CustomerCityCode,$CustomerCity,$CustomerDepto,$CustomerDeptoCode,$CustomerAddress,$CustomerNit,$CustomerIdCode,$CustomerDV,$CustomerEmail);
        $xmlTotal = $invoiceModel->formTotalsXMl($PaymentMeansID,$PaymentMeansCode,$TaxableAmount,$Percent,$TaxAmount,$LineExtensionAmount,$TaxExclusiveAmount,$TaxInclusiveAmount,$PayableAmount,$ChargeTotalAmount);
        $xmlLines = $invoiceModel->formLinesXMl($items);
        $xml = $xmlHead.$xmlExtensions.$xmlVersion.$xmlCompany.$xmlCustomer.$xmlTotal.$xmlLines;
        $firmado = new FirmadorV2();
        // locacion del certificado
        $certificadop12 = '8999990070.p12';
        // clave certificado
        $clavecertificado = '8999990070';
        // prefijo archivo xml en facturas
        $pf = 'fv';
        // firmar factura
        $signed = $firmado->firmar($certificadop12, $clavecertificado, $xml, $UUID, $pf);
        // nombre de archivos
        $nit = '0'.$companyNIT;
        // 000 si es software propio
        $ppp = '000';
        // año en curso
        $aa = '20';
        $a = str_pad($consecutive, 8, '0', STR_PAD_LEFT);
        // nombre del xml
        $xml_name = $pf.$nit.$ppp.$aa.$a.'.xml';
        // prefijo zip
        $z = 'z';
        // nombre zip
        $fileName = $z.$nit.$ppp.$aa.$a.'.zip';
        // nombre final zip con locacion donde se va a guardar
        $zip_name = 'xml/'.$fileName;
        // crear new zip
        $zip = new ZipArchive;
        $zip->open($zip_name, ZipArchive::CREATE);
        // agregar el xml filmado 
        $zip->addFromString($xml_name, $signed);
        $zip->close();
        // get the contents 
        $document = file_get_contents($zip_name);
        // codificar
        $contentFile =  base64_encode($document);   
        $testSetId = '18fcbc18-9728-422e-b393-a4e27b4406a0';
        // xml firmado para web service DIAN
        $action = "http://wcf.dian.colombia/IWcfDianCustomerServices/SendTestSetAsync";
        $soap = $invoiceModel->sendTestSetAsyncXML($fileName,$testSetId,$contentFile,$action);
        $endpoint = "https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc";
        $sign = new SignWebService;
        $response = $sign->sendSOAP($endpoint,$action,$soap);
        dd($response);

        return view('invoice');
    }

    public function getStatus(){
        $invoiceModel = new Invoice();
        $trackId = 'fc8eac422eba16e22ffd8c6f94b3f40a6e38162c';
        $action = "http://wcf.dian.colombia/IWcfDianCustomerServices/GetNumberingRange";
        $soap = $invoiceModel->getStatusXML($trackId,$action);
        $endpoint = "https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc";
        $sign = new SignWebService;
        $response = $sign->sendSOAP($endpoint,$action,$soap);
        dd($response);
    }

    public function getStatusZip(){
        $invoiceModel = new Invoice();
        $trackId = '276bcf62-33cf-403a-8000-45e5c2bfa8bb';
        $action = "http://wcf.dian.colombia/IWcfDianCustomerServices/GetStatusZip";
        $soap = $invoiceModel->getStatusZipXML($trackId,$action);
        $endpoint = "https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc";
        $sign = new SignWebService;
        $response = $sign->sendSOAP($endpoint,$action,$soap);
        return view('obtenerStatusZip')->with('firma', $response); 
       // dd($response);   
    }
//////////////////////////////////////////////////////
   
     public function getBill(){
       
        $invoiceModel = new Invoice();

        // mock base : quitar 
        $invoice = (object) array(
            'fecfactur' => '2004-06-05 00:00:00',
            'tidresp' => 'CC',
            'invoice_number'=> '10',
        );
        // consulta DB        
        // $invoice = $invoiceModel->getInvoiceDesc();
        // // quitar
        // $invoice = $invoice[0];
        // consulta FIN DB

        // data archivo config
        $config=DataConfig::getDataConfig();
        $items = array();
        $user = null;
        $consecutive = 1;
        $office = null;
        $InvoiceAuthorization = null;
        $invoice->fecfactur  = Carbon::parse($invoice->fecfactur);        
        $InvoiceAuthorization = $config['InvoiceAuthorization'];
        $StartDate = $config['StartDate'];
        $EndDate = $config['EndDate'];
        $Prefix = $config['Prefix'];
        $From = $config['From'];
        $To =  $config['To'];
        $companyNIT = $config['companyNIT'];
        $companyNITDV = null;
        $SoftwareID = $config['SoftwareID'];
        $ClTec = $config['ClTec'];
        $pin = $config['pin'];
        $AuthorizationProviderID = $config['companyNIT'];
        $CustomizationID = env('CUSTOMIZATION_ID');
        $ProfileExecutionID = env('PROFILE_EXECUTION_ID');
        $ID = $Prefix.$invoice->invoice_number;
        $SoftwareSecurityCode = hash('sha384', $SoftwareID.$pin );  
        $IssueDate  = $invoice->fecfactur->format('Y-m-d');
        $IssueTime = $invoice->fecfactur->format('h:s:i')."-05:00";
        $InvoiceTypeCode = env('INVOICE_TYPE_CODE');
        $LineCountNumeric = '2';
        // $LineCountNumeric = $items->count(); // TODO: numero de productos?
        $InvoicePeriodStartDate = $invoice->fecfactur->startOfMonth()->toDateString(); 
        $InvoicePeriodEndDate =  $invoice->fecfactur->endOfMonth()->toDateString();
        $IndustryClasificationCode = $config['IndustryClasificationCode'];
        $CompanyName = 'GRUPO FAMILIA S.A.S';
        $CompanyAddress = 'Carrera 4 # 76 - 98';
        $CompanyCity = 'Medellín';
        $CompanyDepto = 'Antioquia';
        $CompanyDeptoCode = '05';
        $companyEmail = null;
        $CompanyPostCode = '193558';
        $TaxLevelCode = ' O-13;O-15';
        $TLClistName = '48';
        $cityCode = '05001';
        $TaxSchemeId = '01';
        $TaxSchemeName = 'IVA';
        $AdditionalAccountID = '1';
        // datos del receptor
        $CustomerName = null;
        $CustomerCity = null;
        $CustomerDepto = null;
        $CustomerAddress = null;
        $CustomerNit = '71603486';
        $CustomerEmail = '71603486';
        // codigo ciudad
        $CustomerCityCode = '05042';
        $CustomerDeptoCode = '05';
        // ver tabla, 31 para nit, 13 para cedula
        $CustomerIdCode = '31';
        // ver tabla 2 para persona natural, 1 para persona juridica
        $AdditionalAccountID = '2';
        // digito de verificacion nit cliente, null por defecto 
        $CustomerDV = null;
        // hay q informarlo si se identifica con nit
        if ($CustomerIdCode == '31') {
            $CustomerDV = $invoiceModel->getDV($CustomerNit);
            $AdditionalAccountID = '1';
        };
        // metodo de pago, ver tabla
        $PaymentMeansID = '1';
        $PaymentMeansCode = '10';
        // totales
        // valor taxeable
        $TaxableAmount = null;
        // total impuestos
        $TaxAmount = null;
        // procentaje de impuesto
        $Percent = '19';
        // valor neto
        $LineExtensionAmount = null;
        // valor taxeabel
        $TaxExclusiveAmount = null;
        // valor con impuestos
        $TaxInclusiveAmount = null;
        // otros cargos
        $ChargeTotalAmount = null;
        // total factura
        $PayableAmount = null;
        // otros impuestos, este codigo no esta diseñado para reportar Ipo Consumo o otros impuestos
        $codImp1 = '01';
        $ValImp1 =  $TaxAmount;
        $codImp2 = '04';
        $ValImp2 =  '0.00';
        $codImp3 = '03';
        $ValImp3 =  '0.00';
        $OtherTaxes = '0.00';
        // numero de productos en factura
        $LineCountNumeric = null;
        $codImp1 = '01';
        // cufe
        $cufe = $ID.$IssueDate.$IssueTime.$LineExtensionAmount.$codImp1.$ValImp1.$codImp2.$ValImp2.$codImp3.$ValImp3.$PayableAmount.$companyNIT.$CustomerNit.$ClTec.$ProfileExecutionID;
        $UUID = hash('sha384',$cufe);
        $QRCode = "NumFac: $ID FecFac: $IssueDate HorFac: $IssueTime NitFac: $companyNIT DocAdq: $CustomerNit ValFac: $LineExtensionAmount ValIva: $TaxAmount ValOtroIm: $OtherTaxes ValTolFac: $PayableAmount CUFE: $UUID https://catalogovpfe.dian.gov.co/document/searchqr?documentkey=$UUID";
        $xmlHead = $invoiceModel->formHeadXMl();
        $xmlExtensions = $invoiceModel->formExtensionXMl($InvoiceAuthorization,$StartDate,$EndDate,$Prefix,$From,$To,$companyNIT,$SoftwareID,$AuthorizationProviderID,$QRCode,$companyNITDV,$SoftwareSecurityCode);
        $xmlVersion = $invoiceModel->formVersionXMl($CustomizationID,$ProfileExecutionID,$ID,$UUID,$IssueDate,$IssueTime,$InvoiceTypeCode,$LineCountNumeric,$InvoicePeriodStartDate,$InvoicePeriodEndDate); 
        $xmlCompany = $invoiceModel->formCompanyXMl($CompanyName,$CompanyCity,$CompanyDepto,$CompanyDeptoCode,$CompanyAddress,$companyNIT,$TaxLevelCode,$cityCode,$TaxSchemeId,$TaxSchemeName,$companyNITDV,$TLClistName,$companyEmail,$Prefix);
        $xmlCustomer = $invoiceModel->formCustomerXMl($AdditionalAccountID,$CustomerName,$CustomerCityCode,$CustomerCity,$CustomerDepto,$CustomerDeptoCode,$CustomerAddress,$CustomerNit,$CustomerIdCode,$CustomerDV,$CustomerEmail);
        $xmlTotal = $invoiceModel->formTotalsXMl($PaymentMeansID,$PaymentMeansCode,$TaxableAmount,$Percent,$TaxAmount,$LineExtensionAmount,$TaxExclusiveAmount,$TaxInclusiveAmount,$PayableAmount,$ChargeTotalAmount);
        $xmlLines = $invoiceModel->formLinesXMl($items);
        $xml = $xmlHead.$xmlExtensions.$xmlVersion.$xmlCompany.$xmlCustomer.$xmlTotal.$xmlLines;
        $firmado = new FirmadorV2();
        // locacion del certificado
        $certificadop12 = '8999990070.p12';
        // clave certificado
        $clavecertificado = '8999990070';
        // prefijo archivo xml en facturas
        $pf = 'fv';
        // firmar factura
        $signed = $firmado->firmar($certificadop12, $clavecertificado, $xml, $UUID, $pf);
        $nit = '0'.$companyNIT;
        // 000 si es software propio
        $ppp = '000';
        // año en curso
        $aa = '20';
        $a = str_pad($consecutive, 8, '0', STR_PAD_LEFT);
        // nombre del xml
        $xml_name = $pf.$nit.$ppp.$aa.$a.'.xml';
        $txt_name = $pf.$nit.$ppp.$aa.$a.'.txt';
        // prefijo zip
        $z = 'z';
        // nombre zip
        $fileName = $z.$nit.$ppp.$aa.$a.'.zip';
        // nombre final zip con locacion donde se va a guardar
        $zip_name = 'xml/'.$fileName;
        // crear new zip
        $zip = new ZipArchive;
        $zip->open($zip_name, ZipArchive::CREATE);
        // agregar el xml filmado 
        $zip->addFromString($xml_name, $signed);
         $zip->close();
        return view('firmarFactura')->with('firma', $signed); 
    }
}

