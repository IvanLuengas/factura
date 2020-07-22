<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\DataConfig;
use Carbon\Carbon;

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
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->getInvoiceDesc();
        // quitar
        $invoice = $invoice[0];
        // data archivo config
        $config=DataConfig::getDataConfig();
        
        $items = null;
        $user = null;

        $invoice->fecfactur  = date_create_from_format('Y-m-d h:s:i',$invoice->fecfactur);
        $InvoiceAuthorization = $config['InvoiceAuthorization'];
        $StartDate = $config['StartDate'];
        $EndDate = $config['EndDate'];
        $Prefix = $config['Prefix'];
        $From = $config['From'];
        $To =  $config['To'];
        $companyNIT = $config['companyNIT'];
        $SoftwareID = $config['SoftwareID'];
        $ClTec = $config['ClTec'];
        $pin = $config['pin'];
        $SoftwareSecurityCode = hash('sha384', $SoftwareID.$pin );
        $AuthorizationProviderID = $config['companyNIT'];
        $CustomizationID = env('CUSTOMIZATION_ID');
        $ProfileExecutionID = env('PROFILE_EXECUTION_ID');
        $ID = $Prefix.$From;
        $IssueDate  = $invoice->fecfactur->format('Y-m-d');
        $IssueTime = $invoice->fecfactur->format('h:s:i')."-05:00";
        $InvoiceTypeCode = env('INVOICE_TYPE_CODE');
        // $LineCountNumeric = $items->count(); // TODO: numero de productos?
        $InvoicePeriodStartDate = $invoice->fecfactur->startOfMonth()->toDateString(); 
        $InvoicePeriodEndDate =  $invoice->fecfactur->endOfMonth()->toDateString();
        var_dump($InvoicePeriodStartDate);
        var_dump($InvoicePeriodEndDate);

        $IndustryClasificationCode = '5440'; 
        $CompanyName = 'GRUPO FAMILIA S.A.S';
        $CompanyAddress = 'Carrera 4 # 76 - 98';
        $CompanyCity = 'Medellín';
        $CompanyDepto = 'Antioquia';
        $CompanyDeptoCode = '05';
        $CompanyPostCode = '193558';
        $TaxLevelCode = ' O-13;O-15';
        $cityCode = '05001';
        $TaxSchemeId = '01';
        $TaxSchemeName = 'IVA';

        $AdditionalAccountID = '1';
        // $CustomerName = $user->name;
        $CustomerCityCode = '05042';
        // $CustomerCity = $user->city;
        // $CustomerDepto = $user->depto;
        $CustomerDeptoCode = '05';
        // $CustomerAddress = $user->address;
        // $CustomerNit = $user->personal_id;
        // $CustomerIdCode = $user->type_of_id;
        $PaymentMeansID = '1';
        $PaymentMeansCode = '10';

        // $TaxableAmount = $invoice->subtotal;
        // $TaxAmount = $invoice->iva;
        $Percent = '19';
        // $LineExtensionAmount = $invoice->totalproducts;
        // $AllowanceTotalAmount= $invoice->discount;
        // $TaxExclusiveAmount= $invoice->subtotal;
        // $TaxInclusiveAmount= $invoice->total;
        // $PayableAmount = $invoice->total;

        $codImp1 = '01';
        // $ValImp1 =  $TaxAmount;

        $codImp2 = '04';
        $ValImp2 =  0.00;

        $codImp3 = '03';
        $ValImp3 =  0.00;
        // $cufe = $ID.$IssueDate.$IssueTime.$LineExtensionAmount.$codImp1.$ValImp1.$codImp2.$ValImp2.$codImp3.$ValImp3.$PayableAmount.$companyNIT.$CustomerNit.$ClTec.$ProfileExecutionID;
        
        // $UUID = hash('sha384', $cufe);

        // $QRCode = "NroFactura=$ID NitFacturador=$companyNIT NitAdquiriente=$CustomerNit FechaFactura=$IssueDate ValorTotalFactura=$PayableAmount CUFE=$UUID URL=https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey=$UUID";

        // $signature = $this->getSignature();


        // $xmlHead = $this->formHeadXMl();
        // $xmlExtensions = $this->formExtensionXMl($InvoiceAuthorization,$StartDate,$EndDate,$Prefix,$From,$To,$companyNIT,$SoftwareID,$AuthorizationProviderID,$QRCode,$signature);

        // $xmlVersion = $this->formVersionXMl($CustomizationID,$ProfileExecutionID,$ID,$UUID,$IssueDate,$IssueTime,$InvoiceTypeCode,$LineCountNumeric,$InvoicePeriodStartDate,$InvoicePeriodEndDate);

        // $xmlCompany = $this->formCompanyXMl($CompanyName,$CompanyPostCode,$CompanyCity,$CompanyDepto,$CompanyDeptoCode,$CompanyAddress,$companyNIT,$TaxLevelCode,$cityCode,$TaxSchemeId,$TaxSchemeName);

        // $xmlCustomer = $this->formCustomerXMl($AdditionalAccountID,$CustomerName,$CustomerCityCode,$CustomerCity,$CustomerDepto,$CustomerDeptoCode,$CustomerAddress,$CustomerNit,$CustomerIdCode);

        // $xmlTotal = $this->formTotalsXMl($PaymentMeansID,$PaymentMeansCode,$TaxableAmount,$Percent,$TaxAmount,$LineExtensionAmount,$AllowanceTotalAmount,$TaxExclusiveAmount,$TaxInclusiveAmount,$PayableAmount);

        // $xmlLines = $this->formLinesXMl($items);

        // $xml = $xmlHead.$xmlExtensions.$xmlVersion.$xmlCompany.$xmlCustomer.$xmlTotal.$xmlLines;


        // $this->validateXML($xml);
        

        // TODO: agregar variables willi
        return view('invoice');
    }

}
