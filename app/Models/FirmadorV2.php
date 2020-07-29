<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Storage;
use DOMDocument;

class FirmadorV2 extends Model
{
    const POLITICA_FIRMA = array(
        "name"      => "Política de firma para facturas electrónicas de la República de Colombia",
        "url"       => "https://facturaelectronica.dian.gov.co/politicadefirma/v2/politicadefirmav2.pdf",
        "digest"    => "dMoMvtcG5aIzgYo0tIsSQeVJBDnUnfSOfBpxXrmor0Y="
    );
    
    private $publicKey        = NULL;
    private $privateKey       = NULL;
    
    
    public function base64Encode($strcadena){
        return base64_encode(hash('sha256' , $strcadena, true));
    }
    
    public function firmar($certificadop12, $clavecertificado, $xmlsinfirma, $UUID, $doctype){
        $pfx = Storage::disk('local')->get($certificadop12);
        openssl_pkcs12_read($pfx, $key, $clavecertificado);
        $this->publicKey          = $key["cert"];
        $this->privateKey         = $key["pkey"];
        $this->signPolicy         = self::POLITICA_FIRMA;
        $this->signatureID        = "xmldsig-".$UUID;
        $this->Reference0Id       = "xmldsig-".$UUID."-ref0";
        $this->KeyInfoId          = "xmldsig-".$UUID."-KeyInfo";
        $this->SignedPropertiesId = "xmldsig-".$UUID. "-signedprops";
        return $this->insertaFirma($xmlsinfirma,$doctype);
    }


    
    
    public function get_schemas($doctype){
        // obtener como una string los schemas heredados por la etiqueta KeyInfo al momento que el sistema haciendo la validacion del documento (DIAN) canonize el elemento para verificar que el digest sea correcto
        // los schemas heredados por SignedInfo y SignedProperties son los mismos 
        $string = '';
        if ($doctype == 'fv') {
            $string .= 'xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" ';
        }else{
            if ($doctype == 'nc') {
              $string .= 'xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2" ';
            }
            if ($doctype == 'nd') {
              $string .= 'xmlns="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2" ';
            }
        }
        $string .= 'xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sts="http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures" xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
        return $string;
    }
    
    public function generateSignedProperties($signTime,$certDigest,$certIssuer,$certSerialNumber){
          // version canonicalizada no es necesario volver a hacerlo
          return '<xades:SignedProperties Id="'.$this->SignedPropertiesId.'">'.
          '<xades:SignedSignatureProperties>'.
              '<xades:SigningTime>'.$signTime.'</xades:SigningTime>' .
              '<xades:SigningCertificate>'.
                  '<xades:Cert>'.
                      '<xades:CertDigest>'.
                          '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"></ds:DigestMethod>'.
                          '<ds:DigestValue>'.$certDigest.'</ds:DigestValue>'.
                      '</xades:CertDigest>'.
                      '<xades:IssuerSerial>' .
                          '<ds:X509IssuerName>'.$certIssuer.'</ds:X509IssuerName>'.
                          '<ds:X509SerialNumber>' .$certSerialNumber.'</ds:X509SerialNumber>' .
                      '</xades:IssuerSerial>'.
                  '</xades:Cert>'.
              '</xades:SigningCertificate>' .
              '<xades:SignaturePolicyIdentifier>'.
                  '<xades:SignaturePolicyId>' .
                      '<xades:SigPolicyId>'.
                          '<xades:Identifier>'.$this->signPolicy['url'].'</xades:Identifier>'.
                          '<xades:Description>'.$this->signPolicy['name'].'</xades:Description>'.
                      '</xades:SigPolicyId>'.
                      '<xades:SigPolicyHash>' .
                          '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"></ds:DigestMethod>'.
                          '<ds:DigestValue>'.$this->signPolicy['digest'].'</ds:DigestValue>'.
                      '</xades:SigPolicyHash>'.
                  '</xades:SignaturePolicyId>' .
              '</xades:SignaturePolicyIdentifier>'.
              '<xades:SignerRole>' .
                '<xades:ClaimedRoles>' .
                  '<xades:ClaimedRole>supplier</xades:ClaimedRole>' .
                '</xades:ClaimedRoles>' .
              '</xades:SignerRole>' .
          '</xades:SignedSignatureProperties>'.
          '</xades:SignedProperties>';
    }
    
    public function getKeyInfo(){
          // version canonicalizada no es necesario volver a hacerlo
          return '<ds:KeyInfo Id="'.$this->KeyInfoId.'">'.
                    '<ds:X509Data>'.
                        '<ds:X509Certificate>'.$this->getCertificate().'</ds:X509Certificate>'.
                    '</ds:X509Data>'.
                 '</ds:KeyInfo>';
    }
    
    public function getSignedInfo($documentDigest,$kInfoDigest,$SignedPropertiesDigest){
          // version canonicalizada no es necesario volver a hacerlo
          return '<ds:SignedInfo>'.
                    '<ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"></ds:CanonicalizationMethod>'.
                    '<ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"></ds:SignatureMethod>'.
                    '<ds:Reference Id="'.$this->Reference0Id.'" URI="">'.
                      '<ds:Transforms><ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"></ds:Transform></ds:Transforms>'.
                      '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"></ds:DigestMethod>'.
                      '<ds:DigestValue>'.$documentDigest.'</ds:DigestValue>'.
                    '</ds:Reference>'.
                    '<ds:Reference URI="#'.$this->KeyInfoId.'">'.
                      '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"></ds:DigestMethod>'.
                      '<ds:DigestValue>'.$kInfoDigest.'</ds:DigestValue>'.
                    '</ds:Reference>'.
                      '<ds:Reference Type="http://uri.etsi.org/01903#SignedProperties" URI="#'.$this->SignedPropertiesId.'">'.
                      '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"></ds:DigestMethod>'.
                      '<ds:DigestValue>'.$SignedPropertiesDigest.'</ds:DigestValue>'.
                    '</ds:Reference>'.
              '</ds:SignedInfo>'; 
    }
    
    public function getIssuer($issuer){
          $certIssuer = array();
          foreach ($issuer as $item => $value){
              $certIssuer[] = $item . '=' . $value;
          }
          $certIssuer = implode(', ', array_reverse($certIssuer));
          return $certIssuer;
      }
    
      public function getCertificate(){
          openssl_x509_export($this->publicKey, $publicPEM);
          $publicPEM = str_replace("-----BEGIN CERTIFICATE-----", "", $publicPEM);
          $publicPEM = str_replace("-----END CERTIFICATE-----", "", $publicPEM);
          $publicPEM = str_replace("\r", "", str_replace("\n", "", $publicPEM));
          return $publicPEM;
      } 
    
      public function insertaFirma($xml,$doctype){
    
          $d = new DOMDocument('1.0','UTF-8');
          $d->loadXML($xml);
          $canonicalXML = $d->C14N();
          $documentDigest = base64_encode(hash('sha256', $canonicalXML, true)); 
    
          $signTime = date('Y-m-d\TH:i:s-05:00');
    
    
          $certData   = openssl_x509_parse($this->publicKey);
          $certDigest = base64_encode(openssl_x509_fingerprint($this->publicKey, "sha256", true));
          $certSerialNumber = $certData['serialNumber'];
          $certIssuer = $this->getIssuer($certData['issuer']);
    
          $SignedProperties = $this->generateSignedProperties($signTime,$certDigest,$certIssuer,$certSerialNumber);
          $SignedPropertiesWithSchemas = str_replace('<xades:SignedProperties', '<xades:SignedProperties '.$this->get_schemas($doctype), $SignedProperties);
          $SignedPropertiesDigest = $this->base64Encode($SignedPropertiesWithSchemas);
    
          $KeyInfo = $this->getKeyInfo();
          $keyInfoWithShemas = str_replace('<ds:KeyInfo', '<ds:KeyInfo '.$this->get_schemas($doctype), $KeyInfo);
          $kInfoDigest = $this->base64Encode($keyInfoWithShemas);
    
          $signedInfo = $this->getSignedInfo($documentDigest,$kInfoDigest,$SignedPropertiesDigest);
          $SignedInfoWithSchemas = str_replace('<ds:SignedInfo', '<ds:SignedInfo '.$this->get_schemas($doctype), $signedInfo);
    
          $algo = "SHA256";
          openssl_sign($SignedInfoWithSchemas, $signatureResult, $this->privateKey, $algo);
          $signatureResult = base64_encode($signatureResult);
    
    
          $s = '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Id="'.$this->signatureID.'">'. $signedInfo. '<ds:SignatureValue>'.$signatureResult.'</ds:SignatureValue>'.$KeyInfo.'<ds:Object><xades:QualifyingProperties Target="#'.$this->signatureID.'">'.$SignedProperties.'</xades:QualifyingProperties></ds:Object></ds:Signature>';
    
          $search = '<ext:ExtensionContent></ext:ExtensionContent>';
          $replace = '<ext:ExtensionContent>'.$s."</ext:ExtensionContent>";
          $signed = str_replace($search, $replace, $canonicalXML);
          return $signed;
    
        }

        public function signSoap($fileName,$xmlFile,$to,$action){
            $toId = 'A761BBC7BD3CF85423157738020456023';
            $to = 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc';
            $digestValue =  $this->getToTagCan($toId,$to);
            $signedInfoTag = $this->getSignedInfoCan($toId,$digestValue);
            $algo = "SHA256";
            openssl_sign($signedInfoTag, $signatureResult, $this->privateKey, $algo);
            $signatureValue = base64_encode($signatureResult);
            $created = gmdate("Y-m-d\TH:i:s\Z" );
            $expires = gmdate("Y-m-d\TH:i:s\Z", time() + 3600 );
            $certificate = $this->getCertificate();
            $signedSoap = $this->formSoapRequest($certificate,$created,$expires,$digestValue,$signatureValue,$action,$to,$fileName,$xmlFile,$toId);
            return $signedSoap;
          }
          
          // obtener el digest de la etiqueta total
          private function getToTagCan($id,$to){
            // etiqueta <to>  canonical
            $to_tag = '<wsa:To xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:wcf="http://wcf.dian.colombia" xmlns:wsa="http://www.w3.org/2005/08/addressing" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wsu:Id="id-'.$id.'">'.$to.'</wsa:To>';    
            $digest = base64_encode(hash('sha256',$to_tag,true));
            return $digest; 
            // es posible dejar este valor 'hard-coded' ya que mientras nada cambie en la etiqueda el valor siempre va a ser el mismo 
          }
        
          private function getSignedInfoCan($id,$digestValue){
            // <SignedInfo> canonical
            return '<ds:SignedInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:wcf="http://wcf.dian.colombia" xmlns:wsa="http://www.w3.org/2005/08/addressing"><ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"><ec:InclusiveNamespaces xmlns:ec="http://www.w3.org/2001/10/xml-exc-c14n#" PrefixList="wsa soap wcf"></ec:InclusiveNamespaces></ds:CanonicalizationMethod><ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"></ds:SignatureMethod><ds:Reference URI="#id-'.$id.'"><ds:Transforms><ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"><ec:InclusiveNamespaces xmlns:ec="http://www.w3.org/2001/10/xml-exc-c14n#" PrefixList="soap wcf"></ec:InclusiveNamespaces></ds:Transform></ds:Transforms><ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"></ds:DigestMethod><ds:DigestValue>'.$digestValue.'</ds:DigestValue></ds:Reference></ds:SignedInfo>';
        
        
          }
        
          private function formSoapRequest($certificate,$created,$expires,$disgestValue,$signatureValue,$action,$to,$fileName,$xmlFile,$toId){
            return  '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:wcf="http://wcf.dian.colombia"><soap:Header xmlns:wsa="http://www.w3.org/2005/08/addressing"><wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsse:BinarySecurityToken EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" wsu:Id="X509-'.$toId.'">'.$certificate.'</wsse:BinarySecurityToken><wsu:Timestamp><wsu:Created>'.$created.'</wsu:Created><wsu:Expires>'.$expires.'</wsu:Expires></wsu:Timestamp><ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#"><ds:SignedInfo><ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"><ec:InclusiveNamespaces PrefixList="wsa soap wcf" xmlns:ec="http://www.w3.org/2001/10/xml-exc-c14n#"/></ds:CanonicalizationMethod><ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><ds:Reference URI="#id-'.$toId.'"><ds:Transforms><ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"><ec:InclusiveNamespaces PrefixList="soap wcf" xmlns:ec="http://www.w3.org/2001/10/xml-exc-c14n#"/></ds:Transform></ds:Transforms><ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><ds:DigestValue>'.$disgestValue.'</ds:DigestValue></ds:Reference></ds:SignedInfo><ds:SignatureValue>'.$signatureValue.'</ds:SignatureValue><ds:KeyInfo><wsse:SecurityTokenReference><wsse:Reference URI="#X509-'.$toId.'" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3"/></wsse:SecurityTokenReference></ds:KeyInfo></ds:Signature></wsse:Security><wsa:Action>'.$action.'</wsa:Action><wsa:To wsu:Id="id-'.$toId.'" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">'.$to.'</wsa:To></soap:Header><soap:Body><wcf:SendBillSync><wcf:fileName>'.$fileName.'</wcf:fileName><wcf:contentFile>'.$xmlFile.'</wcf:contentFile></wcf:SendBillSync></soap:Body></soap:Envelope>';
          }
}
