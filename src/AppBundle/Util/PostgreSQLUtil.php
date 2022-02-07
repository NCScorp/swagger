<?php

namespace AppBundle\Util;


class PostgreSQLUtil {

    /**
     * Converte array do Postgres para array do PHP
     * @param array $data Postgres Array ex: {'2020-12-20','2020-03-19'}
     * @return array ['2020-12-20', '2020-03-19']
     */
    public static function arrayPostgreToPhp($data) {
        return !$data ? null : explode(',', str_replace(['"', '{','}', " "], "", $data));
    }

    /**
     * Converte array do PHP para array do Postgres
     * array $data Postgres Array ex: ['2020-12-20','2020-03-19']
     * @return array {'2020-12-20', '2020-03-19'}
     */
    public static function arrayPhpToPostgre($data) {
        return !$data ? null : '{'.implode(", ", array_map(function ($data) {
            return '"'.$data.'"';
         }, (array) $data)) .'}';
    }
    


}