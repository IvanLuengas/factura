<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Storage;

class DataConfig extends Model
{
    private static $fileConfig = 'config.json';
    /**
     * Retorna datos de configuracion
     *
     */   
    public static function getDataConfig(){
        // archivo config
        $json = Storage::disk('local')->get(static::$fileConfig);
        $json = json_decode($json, true);
        return $json;
    }
    /**
     * valida si el archivo existe
     *
     */
    public static function getDataExist(){
        $exists = Storage::disk('local')->has(static::$fileConfig);
        return $exists;
    }
}
