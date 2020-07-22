<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

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
}
