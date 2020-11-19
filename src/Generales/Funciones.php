<?php
namespace App\Generales;

class Funciones{

    public function urlAmigable($str, $replace = array("'", "@"), $delimiter = '-') {
        setlocale(LC_ALL, 'es_MX.UTF8');
        if (!empty($replace)) {
            $str = str_replace((array) $replace, ' ', $str);
        }

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return $clean;
    }

}