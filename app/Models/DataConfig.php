<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Storage;

class DataConfig extends Model
{
    /**
     * Retorna datos de configuracion
     *
     */
    public static function getDataConfig(){
         // archivo config
         $json = Storage::disk('local')->get('config.json');
         $json = json_decode($json, true);
         return $json;
    }
}
