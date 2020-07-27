<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DOMDocument;
use GuzzleHttp\Client;
use Stenfrank\UBL21dian\BinarySecurityToken\SOAP;
use Storage;

class SignWebService extends Model
{
    public function getSOAP($xmlString,$action,$outPutXml){
        $pathCertificate = env('PATH_CERT');
        $passwors = env('PSPD');

        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlString);

        $soap = new SOAP($pathCertificate, $passwors);
        $soap->Action = $action;

        //Creación del archivo .XML
        $soap->sign($domDocument->saveXML());

        $archivo = file_put_contents($outPutXml, $soap->xml);
        return $soap->xml;
    }

    public function sendSOAP($endpoint,$action,$soap){
        $options = [
            'body'    => $soap,
            'headers' => [
                "Content-Type" => "application/soap+xml",
                "accept" => "*/*",
                "accept-encoding" => "gzip, deflate",
                'action' => $action
            ]
        ];

        // libreria para request http 
        $client = new Client([
            'verify' => false
        ]);

        // hacer el request
        $res = $client->request(
            'POST',
            $endpoint,
            $options
        );

        $response = new DOMDocument;
        // formar un XMl con la respuesta
        $response->loadXML($res->getBody()->getContents());
        // extraer el codigo de respuesta 
        // 00 para aceptado
        // 99 para rechazado
        $code = $response->getElementsByTagName('StatusCode');
        // extraer errores, es null si no hay  
        $errors = $response->getElementsByTagName('ErrorMessage')[0];
        return $response;
    }
}
