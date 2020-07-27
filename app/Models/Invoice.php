<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\SignWebService;

class Invoice extends Model
{
    
    /**
     * Retorna consulta de factura
     *
     */
    public function getInvoiceData(){
        $invoice = DB::select("select * from snfactur WHERE fecfactur = TO_DATE('05-06-2004', 'DD-MM-YYYY')");
        return $invoice;
    }

    public function getInvoiceDesc(){
        $invoice = DB::select("SELECT f.codfactur, f.lfactur, f.fecfactur, f.codpago, decode(estfactur, 1, 'ANULADA', '') estado, f. tidusuar, f.idusuar, f.tidresp, f.idresp, f.codradica, f.totalfactur, DECODE(C.BIVA, 1, '1', '2') TIENEIVA, DECODE(C.BIVA, 1, 'DERECHOS NOTARIALES', 'OTROS - RECAUDOS E IMPUESTOS') TITGRUP, CF.CODCONCEP, CF.CANT, C.NOMCONCEP, C.TIPCONCEP, C.PORCCONCEP, SUM(CF.PAGOCONCEP) FROM snfactur f, SNCONCEP C, SNCONFAC CF WHERE (f.codfactur >= 20047501 and f.codfactur <= 20047501) and CF.CODFACTUR = f.CODFACTUR AND CF.CODCONCEP = C.CODCONCEP AND CF.PAGOCONCEP > 0 GROUP BY f.codfactur, f.lfactur, f.fecfactur, f.codpago, f.estfactur, f. tidusuar, f.idusuar, f.tidresp, f.idresp, f.codradica, f.totalfactur, DECODE(C.BIVA, 1, '1', '2'), DECODE(C.BIVA, 1, 'DERECHOS NOTARIALES', 'OTROS - RECAUDOS E IMPUESTOS'), CF.CODCONCEP, CF.CANT, C.NOMCONCEP, C.TIPCONCEP, C.PORCCONCEP UNION SELECT f.codfactur, f.lfactur, f.fecfactur, f.codpago, decode(estfactur, 1, 'ANULADA', '') estado, f. tidusuar, f.idusuar, f.tidresp, f.idresp, f.codradica, f.totalfactur, '' TIENEIVA, '' TITGRUP, '' CODCONCEP, 0 CANT, 'ESCRITURA EXENTA DE PAGO' NOMCONCEP, '' TIPCONCEP, 0, 0 FROM snfactur f WHERE (f.codfactur >= 20047501 and f.codfactur <= 20047501) and totalfactur = 0 ORDER BY codfactur, CODCONCEP");
        return $invoice;
    }

    public function sendTestSetAsyncXML($fileName,$testSetId,$contentFile,$action){
        $xmlString = <<<XML
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:wcf="http://wcf.dian.colombia">
            <soap:Header/>
            <soap:Body>
                <wcf:SendTestSetAsync>
                    <!--Optional:-->
                    <wcf:fileName>$fileName</wcf:fileName>
                    <!--Optional:-->
                    <wcf:contentFile>$contentFile</wcf:contentFile>
                    <!--Optional:-->
                    <wcf:testSetId>$testSetId</wcf:testSetId>
                </wcf:SendTestSetAsync>
            </soap:Body>
        </soap:Envelope>
        XML;
        //Certificado y contraseña que se utiliza para la creación del certificado
        $sign = new SignWebService;
        $outPutXml = "xml/SendTestSetAsync.xml";
        return $sign->getSOAP($xmlString,$action,$outPutXml);
    }

    public function getStatusXML($trackId,$action){
        $xmlString = <<<XML
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:wcf="http://wcf.dian.colombia">
        <soap:Header/>
            <soap:Body>
                <wcf:GetStatus>
                    <wcf:trackId>$trackId</wcf:trackId>
                </wcf:GetStatus>
            </soap:Body>
        </soap:Envelope>
        XML;
        //Certificado y contraseña que se utiliza para la creación del certificado
        $sign = new SignWebService;
        $outPutXml = "xml/GetStatus.xml";
        return $sign->getSOAP($xmlString,$action,$outPutXml);
    }

    public function getStatusZipXML($trackId,$action){
        $xmlString = <<<XML
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:wcf="http://wcf.dian.colombia">
        <soap:Header/>
            <soap:Body>
                <wcf:GetStatusZip>
                    <wcf:trackId>$trackId</wcf:trackId>
                </wcf:GetStatusZip>
            </soap:Body>
        </soap:Envelope>
        XML;
        //Certificado y contraseña que se utiliza para la creación del certificado
        $sign = new SignWebService;
        $outPutXml = "xml/GetStatusZip.xml";
        return $sign->getSOAP($xmlString,$action,$outPutXml);
    }

    public function getDV($nit){
        $b = 11;
        $nit = strrev($nit); 
        $vpri = [3,7,13,17,19,23,29,37,41,43,47,53,59,67,71];
        $z = strlen($nit) ;
        $x = 0 ;
        for ($i=0; $i < $z; $i++) { 
          $y =  substr($nit, $i, 1);
          $n =  $vpri[$i];
          $x += ( $y * $n) ;
        }     
        $r = $x % $b;
        $dv = ( $r > 1 ) ? $b - $r : $r; 
        return $dv;
    }

    public function geterrors(){
        $errors = libxml_get_errors();
        foreach ($errors as $key => $value) {
          echo $this->libxml_display_error($value);
        }
        libxml_clear_errors();
    }
    
    public function validateXML($doc){
        libxml_use_internal_errors(true);
        $xml = new DOMDocument();
        $xml->loadXML($doc);
        $validator_doc = 'xsd/UBL-Invoice-2.1.xsd';
        if ( $xml->schemaValidate($validator_doc) ) {
          echo 'La validacion paso!';   
        }else{
          $this->geterrors();
          echo 'erorr'; 
        } 
    }

    public function formLinesXMl($items){

        $string = "";
    
        foreach ($items as $key => $value) {
    
          $LineID = $key+1;
          $LineQty = null;
          $AllowanceChargeID = 1;
          $LineBaseAmount = null;
          $AllowancePercentage = null;
          $LineAllowanceAmount = null;
          $LineTotal = null;
          $LineTax = null;
          $LineTaxPercentage = null;
          $LineItemName =  null;
          if ($LineTax > 0) {
            $TaxableAmount = $LineTotal;
          }else{
            $TaxableAmount = 0;
          }
    
          $t += $TaxableAmount; 
          $string .= 
          "<cac:InvoiceLine> 
            <cbc:ID>$LineID</cbc:ID> 
            <cbc:InvoicedQuantity unitCode='EA'>$LineQty</cbc:InvoicedQuantity> 
            <cbc:LineExtensionAmount currencyID='COP'>$LineTotal</cbc:LineExtensionAmount>"; 
    
          if ($LineTotal == 0) {
    
            $total_list_price = $LineBaseAmount*$LineQty;
            $string .=  
            "<cac:PricingReference>
              <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID='COP'>$total_list_price</cbc:PriceAmount>
                <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
              </cac:AlternativeConditionPrice>
            </cac:PricingReference>";
          }    
    
          $string .=  
          "<cac:AllowanceCharge> 
                <cbc:ID>$AllowanceChargeID</cbc:ID> 
                <cbc:ChargeIndicator>false</cbc:ChargeIndicator> 
                <cbc:MultiplierFactorNumeric>$AllowancePercentage</cbc:MultiplierFactorNumeric> 
                <cbc:Amount currencyID='COP'>$LineAllowanceAmount</cbc:Amount> 
                <cbc:BaseAmount currencyID='COP'>$LineBaseAmount</cbc:BaseAmount> 
              </cac:AllowanceCharge>
              <cac:TaxTotal> 
                <cbc:TaxAmount currencyID='COP'>$LineTax</cbc:TaxAmount> 
                <cac:TaxSubtotal> 
                  <cbc:TaxableAmount currencyID='COP'>$TaxableAmount</cbc:TaxableAmount> 
                  <cbc:TaxAmount currencyID='COP'>$LineTax</cbc:TaxAmount> 
                  <cac:TaxCategory> 
                    <cbc:Percent>$LineTaxPercentage</cbc:Percent> 
                    <cac:TaxScheme> 
                      <cbc:ID>01</cbc:ID> 
                      <cbc:Name>IVA</cbc:Name> 
                    </cac:TaxScheme> 
                  </cac:TaxCategory> 
                </cac:TaxSubtotal> 
              </cac:TaxTotal> 
              <cac:Item> 
                <cbc:Description>$LineItemName</cbc:Description> 
              </cac:Item> 
              <cac:Price> 
                <cbc:PriceAmount currencyID='COP'>$LineTotal</cbc:PriceAmount> 
                <cbc:BaseQuantity unitCode='EA'>$LineQty</cbc:BaseQuantity> 
              </cac:Price> 
            </cac:InvoiceLine>";
    
        }
    
    
        return $string."</Invoice>";
    
    }

    public function formTotalsXMl($PaymentMeansID,$PaymentMeansCode,$TaxableAmount,$Percent,$TaxAmount,$LineExtensionAmount,$TaxExclusiveAmount,$TaxInclusiveAmount,$PayableAmount,$ChargeTotalAmount){

        $string = 
          "<cac:PaymentMeans> 
            <cbc:ID>$PaymentMeansID</cbc:ID> 
              <cbc:PaymentMeansCode>$PaymentMeansCode</cbc:PaymentMeansCode> 
            </cac:PaymentMeans>"; 
    
        if ($ChargeTotalAmount > 0 ){
         $string .=   
            "<cac:AllowanceCharge>
              <cbc:ID>1</cbc:ID>
              <cbc:ChargeIndicator>true</cbc:ChargeIndicator>  
              <cbc:AllowanceChargeReason>ENVIO - PROCESAMIENTO</cbc:AllowanceChargeReason>  
              <cbc:Amount currencyID='COP'>$ChargeTotalAmount</cbc:Amount> 
            </cac:AllowanceCharge>";
        }
    
        $string .=
            "<cac:TaxTotal> 
              <cbc:TaxAmount currencyID='COP'>$TaxAmount</cbc:TaxAmount> 
              <cac:TaxSubtotal> 
                <cbc:TaxableAmount currencyID='COP'>$TaxableAmount</cbc:TaxableAmount> 
                <cbc:TaxAmount currencyID='COP'>$TaxAmount</cbc:TaxAmount> 
                <cac:TaxCategory> 
                  <cbc:Percent>$Percent</cbc:Percent> 
                  <cac:TaxScheme> 
                    <cbc:ID>01</cbc:ID> 
                    <cbc:Name>IVA</cbc:Name> 
                  </cac:TaxScheme> 
                </cac:TaxCategory> 
              </cac:TaxSubtotal> 
            </cac:TaxTotal> 
            <cac:LegalMonetaryTotal> 
              <cbc:LineExtensionAmount currencyID='COP'>$LineExtensionAmount</cbc:LineExtensionAmount> 
              <cbc:TaxExclusiveAmount currencyID='COP'>$TaxExclusiveAmount</cbc:TaxExclusiveAmount> 
              <cbc:TaxInclusiveAmount currencyID='COP'>$TaxInclusiveAmount</cbc:TaxInclusiveAmount> 
              <cbc:ChargeTotalAmount currencyID='COP'>$ChargeTotalAmount</cbc:ChargeTotalAmount> 
              <cbc:PayableAmount currencyID='COP'>$PayableAmount</cbc:PayableAmount> 
            </cac:LegalMonetaryTotal>";
    
        return $string;
    }

    public function formCustomerXMl($AdditionalAccountID,$CustomerName,$CustomerCityCode,$CustomerCity,$CustomerDepto,$CustomerDeptoCode,$CustomerAddress,$CustomerNit,$customerIdCode,$CustomerDV,$CustomerEmail){
        return   
        "<cac:AccountingCustomerParty>
          <cbc:AdditionalAccountID>$AdditionalAccountID</cbc:AdditionalAccountID>
              <cac:Party> 
                  <cac:PartyName> 
                      <cbc:Name>$CustomerName</cbc:Name>
                  </cac:PartyName> 
                  <cac:PhysicalLocation> 
                      <cac:Address> 
                          <cbc:ID>$CustomerCityCode</cbc:ID> 
                          <cbc:CityName>$CustomerCity</cbc:CityName> 
                          <cbc:CountrySubentity>$CustomerDepto</cbc:CountrySubentity> 
                          <cbc:CountrySubentityCode>$CustomerDeptoCode</cbc:CountrySubentityCode> 
                          <cac:AddressLine> 
                              <cbc:Line>$CustomerAddress</cbc:Line> 
                          </cac:AddressLine> 
                          <cac:Country> 
                              <cbc:IdentificationCode>CO</cbc:IdentificationCode> 
                              <cbc:Name languageID='es'>Colombia</cbc:Name> 
                          </cac:Country> 
                      </cac:Address> 
                  </cac:PhysicalLocation> 
                  <cac:PartyTaxScheme> 
                      <cbc:RegistrationName>$CustomerName</cbc:RegistrationName> 
                      <cbc:CompanyID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)' schemeName='$customerIdCode' schemeID='$CustomerDV'>$CustomerNit</cbc:CompanyID> 
                      <cac:TaxScheme> 
                          <cbc:ID>ZY</cbc:ID> 
                          <cbc:Name>No Causa</cbc:Name> 
                      </cac:TaxScheme> 
                  </cac:PartyTaxScheme> 
                  <cac:PartyLegalEntity> 
                      <cbc:RegistrationName>$CustomerName</cbc:RegistrationName> 
                      <cbc:CompanyID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)' schemeID='$CustomerDV' schemeName='$customerIdCode'>$CustomerNit</cbc:CompanyID>
                  </cac:PartyLegalEntity> 
                  <cac:Contact>
                      <cbc:ElectronicMail>
                          $CustomerEmail
                      </cbc:ElectronicMail>
                  </cac:Contact>
              </cac:Party> 
          </cac:AccountingCustomerParty>";
  
    }

    public function formCompanyXMl($CompanyName,$CompanyCity,$CompanyDepto,$CompanyDeptoCode,$CompanyAddress,$companyNIT,$TaxLevelCode,$cityCode,$TaxSchemeId,$TaxSchemeName,$companyNITDV,$TLClistName,$companyEmail,$Prefix){
        $string = 
        "<cac:AccountingSupplierParty> 
           <cbc:AdditionalAccountID>1</cbc:AdditionalAccountID> 
           <cac:Party> 
               <cac:PartyName> 
                   <cbc:Name>$CompanyName</cbc:Name> 
               </cac:PartyName>
               <cac:PhysicalLocation>
                   <cac:Address>
                       <cbc:ID>$cityCode</cbc:ID>
                       <cbc:CityName>$CompanyCity</cbc:CityName>
                       <cbc:CountrySubentity>$CompanyDepto</cbc:CountrySubentity>
                       <cbc:CountrySubentityCode>$CompanyDeptoCode</cbc:CountrySubentityCode>
                       <cac:AddressLine>
                           <cbc:Line>$CompanyAddress</cbc:Line>
                       </cac:AddressLine>
                       <cac:Country>
                           <cbc:IdentificationCode>CO</cbc:IdentificationCode>
                           <cbc:Name languageID='es'>Colombia</cbc:Name>
                       </cac:Country>
                   </cac:Address>
               </cac:PhysicalLocation> 
               <cac:PartyTaxScheme> 
                   <cbc:RegistrationName>$CompanyName</cbc:RegistrationName> 
                   <cbc:CompanyID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)' schemeID='$companyNITDV' schemeName='31'>$companyNIT</cbc:CompanyID> 
                   <cbc:TaxLevelCode listName='$TLClistName'>$TaxLevelCode</cbc:TaxLevelCode> 
                   <cac:RegistrationAddress> 
                       <cbc:ID>$cityCode</cbc:ID> 
                       <cbc:CityName>$CompanyCity</cbc:CityName> 
                       <cbc:CountrySubentity>$CompanyDepto</cbc:CountrySubentity> 
                       <cbc:CountrySubentityCode>$CompanyDeptoCode</cbc:CountrySubentityCode> 
                       <cac:AddressLine> 
                           <cbc:Line>$CompanyAddress</cbc:Line> 
                       </cac:AddressLine> 
                       <cac:Country> 
                           <cbc:IdentificationCode>CO</cbc:IdentificationCode> 
                           <cbc:Name languageID='es'>Colombia</cbc:Name> 
                       </cac:Country> 
                   </cac:RegistrationAddress> 
                   <cac:TaxScheme> 
                       <cbc:ID>$TaxSchemeId</cbc:ID> 
                       <cbc:Name>$TaxSchemeName</cbc:Name> 
                   </cac:TaxScheme> 
               </cac:PartyTaxScheme> 
               <cac:PartyLegalEntity> 
                   <cbc:RegistrationName>$CompanyName</cbc:RegistrationName> 
                   <cbc:CompanyID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)' schemeID='$companyNITDV' schemeName='31'>$companyNIT</cbc:CompanyID>
                   <cac:CorporateRegistrationScheme>
                     <cbc:ID>$Prefix</cbc:ID>
                   </cac:CorporateRegistrationScheme> 
               </cac:PartyLegalEntity> 
               <cac:Contact>
                   <cbc:ElectronicMail>$companyEmail</cbc:ElectronicMail>
               </cac:Contact>
           </cac:Party> 
       </cac:AccountingSupplierParty>";
        return $string;
    }

    public function formVersionXMl($CustomizationID,$ProfileExecutionID,$ID,$UUID,$IssueDate,$IssueTime,$InvoiceTypeCode,$LineCountNumeric,$InvoicePeriodStartDate,$InvoicePeriodEndDate){
        $string = "<cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID> <cbc:CustomizationID>$CustomizationID</cbc:CustomizationID> <cbc:ProfileID>DIAN 2.1</cbc:ProfileID> <cbc:ProfileExecutionID>$ProfileExecutionID</cbc:ProfileExecutionID> <cbc:ID>$ID</cbc:ID> <cbc:UUID schemeID='$ProfileExecutionID' schemeName='CUFE-SHA384'>$UUID</cbc:UUID> <cbc:IssueDate>$IssueDate</cbc:IssueDate> <cbc:IssueTime>$IssueTime</cbc:IssueTime> <cbc:InvoiceTypeCode>$InvoiceTypeCode</cbc:InvoiceTypeCode> <cbc:DocumentCurrencyCode listAgencyID='6' listAgencyName='United Nations Economic Commission for Europe' listID='ISO 4217 Alpha'>COP</cbc:DocumentCurrencyCode> <cbc:LineCountNumeric>$LineCountNumeric</cbc:LineCountNumeric> <cac:InvoicePeriod> <cbc:StartDate>$InvoicePeriodStartDate</cbc:StartDate> <cbc:EndDate>$InvoicePeriodEndDate</cbc:EndDate> </cac:InvoicePeriod>";
        return $string;
    }

    public function formExtensionXMl($InvoiceAuthorization,$StartDate,$EndDate,$Prefix,$From,$To,$companyNIT,$SoftwareID,$AuthorizationProviderID,$QRCode,$companyNITDV,$SoftwareSecurityCode){
        return 
        "<ext:UBLExtensions> 
            <ext:UBLExtension> 
                <ext:ExtensionContent> 
                    <sts:DianExtensions> 
                        <sts:InvoiceControl> 
                            <sts:InvoiceAuthorization>$InvoiceAuthorization</sts:InvoiceAuthorization> 
                            <sts:AuthorizationPeriod> 
                                <cbc:StartDate>$StartDate</cbc:StartDate> 
                                <cbc:EndDate>$EndDate</cbc:EndDate> 
                            </sts:AuthorizationPeriod> 
                            <sts:AuthorizedInvoices> 
                                <sts:Prefix>$Prefix</sts:Prefix> 
                                <sts:From>$From</sts:From> 
                                <sts:To>$To</sts:To> 
                            </sts:AuthorizedInvoices> 
                        </sts:InvoiceControl> 
                        <sts:InvoiceSource> 
                            <cbc:IdentificationCode listAgencyID='6' listAgencyName='United Nations Economic Commission for Europe' listSchemeURI='urn:oasis:names:specification:ubl:codelist:gc:CountryIdentificationCode-2.1'>CO</cbc:IdentificationCode> 
                        </sts:InvoiceSource> 
                        <sts:SoftwareProvider> 
                            <sts:ProviderID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)' schemeID='$companyNITDV' schemeName='31'>$companyNIT</sts:ProviderID> 
                            <sts:SoftwareID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)'>$SoftwareID</sts:SoftwareID> 
                        </sts:SoftwareProvider> 
                        <sts:SoftwareSecurityCode schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)'>$SoftwareSecurityCode</sts:SoftwareSecurityCode> 
                        <sts:AuthorizationProvider> 
                            <sts:AuthorizationProviderID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)' schemeID='4' schemeName='31'>$AuthorizationProviderID</sts:AuthorizationProviderID> 
                            </sts:AuthorizationProvider> 
                            <sts:QRCode> $QRCode </sts:QRCode> 
                    </sts:DianExtensions> 
                </ext:ExtensionContent> 
            </ext:UBLExtension> 
            <ext:UBLExtension> <ext:ExtensionContent></ext:ExtensionContent> </ext:UBLExtension> </ext:UBLExtensions>";
    }

    public function formHeadXMl(){
        $string = "<?xml version='1.0' encoding='UTF-8' standalone='no'?><Invoice xmlns='urn:oasis:names:specification:ubl:schema:xsd:Invoice-2' xmlns:cac='urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2' xmlns:cbc='urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2' xmlns:ds='http://www.w3.org/2000/09/xmldsig#' xmlns:ext='urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2' xmlns:sts='http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures' xmlns:xades='http://uri.etsi.org/01903/v1.3.2#' xmlns:xades141='http://uri.etsi.org/01903/v1.4.1#' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='urn:oasis:names:specification:ubl:schema:xsd:Invoice-2 http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd'>";
        return $string;
    }

    public function libxml_display_error($error){
        $return = "<br/>\n";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "<b>Warning $error->code</b>: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "<b>Error $error->code</b>: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "<b>Fatal Error $error->code</b>: ";
                break;
        }
        $return .= trim($error->message);
        if ($error->file) {
            $return .=    " in <b>$error->file</b>";
        }
        $return .= " on line <b>$error->line</b>\n\n\n";
        return $return;
    }

    public function getSignature(){
        return '<ds:Signature Id="xmldsig-d0322c4f-be87-495a-95d5-9244980495f4"> <ds:SignedInfo> <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/> <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/> <ds:Reference Id="xmldsig-d0322c4f-be87-495a-95d5-9244980495f4-ref0" URI=""> <ds:Transforms> <ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/> </ds:Transforms> <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/> <ds:DigestValue>akcOQ5qEh4dkMwt0d5BoXRR8Bo4vdy9DBZtfF5O0SsA=</ds:DigestValue> </ds:Reference> <ds:Reference URI="#xmldsig-87d128b5-aa31-4f0b-8e45-3d9cfa0eec26-keyinfo"> <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/> <ds:DigestValue>troRYR2fcmJLV6gYibVM6XlArbddSCkjYkACZJP47/4=</ds:DigestValue> </ds:Reference> <ds:Reference Type="http://uri.etsi.org/01903#SignedProperties" URI="#xmldsig-d0322c4f-be87-495a-95d5-9244980495f4-signedprops"> <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/> <ds:DigestValue>hpIsyD/08hVUc1exnfEyhGyKX5s3pUPbpMKmPhkPPqU=</ds:DigestValue> </ds:Reference> </ds:SignedInfo> <ds:SignatureValue Id="xmldsig-d0322c4f-be87-495a-95d5-9244980495f4-sigvalue"> q4HWeb47oLdDM4D3YiYDOSXE4YfSHkQKxUfSYiEiPuP2XWvD7ELZTC4ENFv6krgDAXczmi0W7OMi LIVvuFz0ohPUc4KNlUEzqSBHVi6sC34sCqoxuRzOmMEoCB9Tr4VICxU1Ue9XhgP7o6X4f8KFAQWW NaeTtA6WaO/yUtq91MKP59aAnFMfYl8lXpaS0kpUwuui3wdCZsGycsl1prEWiwzpaukEUOXyTo7o RBOuNsDIUhP24Fv1alRFnX6/9zEOpRTs4rEQKN3IQnibF757LE/nnkutElZHTXaSV637gpHjXoUN 5JrUwTNOXvmFS98N6DczCQfeNuDIozYwtFVlMw== </ds:SignatureValue> <ds:KeyInfo Id="xmldsig-87d128b5-aa31-4f0b-8e45-3d9cfa0eec26-keyinfo"> <ds:X509Data> <ds:X509Certificate> MIIIODCCBiCgAwIBAgIIbAsHYmJtoOIwDQYJKoZIhvcNAQELBQAwgbQxIzAhBgkqhkiG9w0BCQEW FGluZm9AYW5kZXNzY2QuY29tLmNvMSMwIQYDVQQDExpDQSBBTkRFUyBTQ0QgUy5BLiBDbGFzZSBJ STEwMC4GA1UECxMnRGl2aXNpb24gZGUgY2VydGlmaWNhY2lvbiBlbnRpZGFkIGZpbmFsMRMwEQYD VQQKEwpBbmRlcyBTQ0QuMRQwEgYDVQQHEwtCb2dvdGEgRC5DLjELMAkGA1UEBhMCQ08wHhcNMTcw OTE2MTM0ODE5WhcNMjAwOTE1MTM0ODE5WjCCARQxHTAbBgNVBAkTFENhbGxlIEZhbHNhIE5vIDEy IDM0MTgwNgYJKoZIhvcNAQkBFilwZXJzb25hX2p1cmlkaWNhX3BydWViYXMxQGFuZGVzc2NkLmNv bS5jbzEsMCoGA1UEAxMjVXN1YXJpbyBkZSBQcnVlYmFzIFBlcnNvbmEgSnVyaWRpY2ExETAPBgNV BAUTCDExMTExMTExMRkwFwYDVQQMExBQZXJzb25hIEp1cmlkaWNhMSgwJgYDVQQLEx9DZXJ0aWZp Y2FkbyBkZSBQZXJzb25hIEp1cmlkaWNhMQ8wDQYDVQQHEwZCb2dvdGExFTATBgNVBAgTDEN1bmRp bmFtYXJjYTELMAkGA1UEBhMCQ08wggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQC0Dn8t oZ2CXun+63zwYecJ7vNmEmS+YouH985xDek7ImeE9lMBHXE1M5KDo7iT/tUrcFwKj717PeVL52Nt B6WU4+KBt+nrK+R+OSTpTno5EvpzfIoS9pLI74hHc017rY0wqjl0lw+8m7fyLfi/JO7AtX/dthS+ MKHIcZ1STPlkcHqmbQO6nhhr/CGl+tKkCMrgfEFIm1kv3bdWqk3qHrnFJ6s2GoVNZVCTZW/mOzPC NnnUW12LDd/Kd+MjN6aWbP0D/IJbB42Npqv8+/oIwgCrbt0sS1bysUgdT4im9bBhb00MWVmNRBBe 3pH5knzkBid0T7TZsPCyiMBstiLT3yfpAgMBAAGjggLpMIIC5TAMBgNVHRMBAf8EAjAAMB8GA1Ud IwQYMBaAFKhLtPQLp7Zb1KAohRCdBBMzxKf3MDcGCCsGAQUFBwEBBCswKTAnBggrBgEFBQcwAYYb aHR0cDovL29jc3AuYW5kZXNzY2QuY29tLmNvMIIB4wYDVR0gBIIB2jCCAdYwggHSBg0rBgEEAYH0 SAECCQIFMIIBvzBBBggrBgEFBQcCARY1aHR0cDovL3d3dy5hbmRlc3NjZC5jb20uY28vZG9jcy9E UENfQW5kZXNTQ0RfVjIuNS5wZGYwggF4BggrBgEFBQcCAjCCAWoeggFmAEwAYQAgAHUAdABpAGwA aQB6AGEAYwBpAPMAbgAgAGQAZQAgAGUAcwB0AGUAIABjAGUAcgB0AGkAZgBpAGMAYQBkAG8AIABl AHMAdADhACAAcwB1AGoAZQB0AGEAIABhACAAbABhAHMAIABQAG8AbADtAHQAaQBjAGEAcwAgAGQA ZQAgAEMAZQByAHQAaQBmAGkAYwBhAGQAbwAgAGQAZQAgAFAAZQByAHMAbwBuAGEAIABKAHUAcgDt AGQAaQBjAGEAIAAoAFAAQwApACAAeQAgAEQAZQBjAGwAYQByAGEAYwBpAPMAbgAgAGQAZQAgAFAA cgDhAGMAdABpAGMAYQBzACAAZABlACAAQwBlAHIAdABpAGYAaQBjAGEAYwBpAPMAbgAgACgARABQ AEMAKQAgAGUAcwB0AGEAYgBsAGUAYwBpAGQAYQBzACAAcABvAHIAIABBAG4AZABlAHMAIABTAEMA RDAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwQwRgYDVR0fBD8wPTA7oDmgN4Y1aHR0cDov L3d3dy5hbmRlc3NjZC5jb20uY28vaW5jbHVkZXMvZ2V0Q2VydC5waHA/Y3JsPTEwHQYDVR0OBBYE FL9BXJHmFVE5c5Ai8B1bVBWqXsj7MA4GA1UdDwEB/wQEAwIE8DANBgkqhkiG9w0BAQsFAAOCAgEA b/pa7yerHOu1futRt8QTUVcxCAtK9Q00u7p4a5hp2fVzVrhVQIT7Ey0kcpMbZVPgU9X2mTHGfPdb R0hYJGEKAxiRKsmAwmtSQgWh5smEwFxG0TD1chmeq6y0GcY0lkNA1DpHRhSK368vZlO1p2a6S13Y 1j3tLFLqf5TLHzRgl15cfauVinEHGKU/cMkjLwxNyG1KG/FhCeCCmawATXWLgQn4PGgvKcNrz+y0 cwldDXLGKqriw9dce2Zerc7OCG4/XGjJ2PyZOJK9j1VYIG4pnmoirVmZbKwWaP4/TzLs6LKaJ4b6 6xLxH3hUtoXCzYQ5ehYyrLVwCwTmKcm4alrEht3FVWiWXA/2tj4HZiFoG+I1OHKmgkNv7SwHS7z9 tFEFRaD3W3aD7vwHEVsq2jTeYInE0+7r2/xYFZ9biLBrryl+q22zM5W/EJq6EJPQ6SM/eLqkpzqM EF5OdcJ5kIOxLbrIdOh0+grU2IrmHXr7cWNP6MScSL7KSxhjPJ20F6eqkO1Z/LAxqNslBIKkYS24 VxPbXu0pBXQvu+zAwD4SvQntIG45y/67h884I/tzYOEJi7f6/NFAEuV+lokw/1MoVsEgFESASI9s N0DfUniabyrZ3nX+LG3UFL1VDtDPWrLTNKtb4wkKwGVwqtAdGFcE+/r/1WG0eQ64xCq0NLutCxg= </ds:X509Certificate> </ds:X509Data> </ds:KeyInfo> <ds:Object> <xades:QualifyingProperties Target="#xmldsig-d0322c4f-be87-495a-95d5-9244980495f4"> <xades:SignedProperties Id="xmldsig-d0322c4f-be87-495a-95d5-9244980495f4-signedprops"> <xades:SignedSignatureProperties> <xades:SigningTime>2019-06-21T19:09:35.993-05:00</xades:SigningTime> <xades:SigningCertificate> <xades:Cert> <xades:CertDigest> <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/> <ds:DigestValue>nem6KXhqlV0A0FK5o+MwJZ3Y1aHgmL1hDs/RMJu7HYw=</ds:DigestValue> </xades:CertDigest> <xades:IssuerSerial> <ds:X509IssuerName> C=CO,L=Bogota D.C.,O=Andes SCD.,OU=Division de certificacion entidad final,CN=CA ANDES SCD S.A. Clase II,1.2.840.113549.1.9.1=#1614696e666f40616e6465737363642e636f6d2e636f </ds:X509IssuerName> <ds:X509SerialNumber>7785324499979575522</ds:X509SerialNumber> </xades:IssuerSerial> </xades:Cert> <xades:Cert> <xades:CertDigest> <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/> <ds:DigestValue>oEsyOEeUGTXr45Jr0jHJx3l/9CxcsxPMOTarEiXOclY=</ds:DigestValue> </xades:CertDigest> <xades:IssuerSerial> <ds:X509IssuerName> C=CO,L=Bogota D.C.,O=Andes SCD,OU=Division de certificacion,CN=ROOT CA ANDES SCD S.A.,1.2.840.113549.1.9.1=#1614696e666f40616e6465737363642e636f6d2e636f </ds:X509IssuerName> <ds:X509SerialNumber>8136867327090815624</ds:X509SerialNumber> </xades:IssuerSerial> </xades:Cert> <xades:Cert> <xades:CertDigest> <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/> <ds:DigestValue>Cs7emRwtXWVYHJrqS9eXEXfUcFyJJBqFhDFOetHu8ts=</ds:DigestValue> </xades:CertDigest> <xades:IssuerSerial> <ds:X509IssuerName> C=CO,L=Bogota D.C.,O=Andes SCD,OU=Division de certificacion,CN=ROOT CA ANDES SCD S.A.,1.2.840.113549.1.9.1=#1614696e666f40616e6465737363642e636f6d2e636f </ds:X509IssuerName> <ds:X509SerialNumber>3184328748892787122</ds:X509SerialNumber> </xades:IssuerSerial> </xades:Cert> </xades:SigningCertificate> <xades:SignaturePolicyIdentifier> <xades:SignaturePolicyId> <xades:SigPolicyId> <xades:Identifier> https://facturaelectronica.dian.gov.co/politicadefirma/v1/politicadefirmav2.pdf </xades:Identifier> </xades:SigPolicyId> <xades:SigPolicyHash> <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/> <ds:DigestValue>dMoMvtcG5aIzgYo0tIsSQeVJBDnUnfSOfBpxXrmor0Y=</ds:DigestValue> </xades:SigPolicyHash> </xades:SignaturePolicyId> </xades:SignaturePolicyIdentifier> <xades:SignerRole> <xades:ClaimedRoles> <xades:ClaimedRole>supplier</xades:ClaimedRole> </xades:ClaimedRoles> </xades:SignerRole> </xades:SignedSignatureProperties> </xades:SignedProperties> </xades:QualifyingProperties> </ds:Object> </ds:Signature>';
    }
}
