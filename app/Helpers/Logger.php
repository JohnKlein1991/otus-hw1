<?php


namespace App\Helpers;


class Logger
{
    public static $logPath = '';

    public static function logSetup($pathFile) {
        self::$logPath = $pathFile;
    }
    /**
     * Метод для логирования отладночной информации
     * @param string $str строка для записи
     */
    public static function log($str) {
        if (self::$logPath == '') self::$logPath = dirname(@$_SERVER['PWD'].$_SERVER['SCRIPT_FILENAME']).'/'.basename( $_SERVER['SCRIPT_FILENAME'], '.php').'.log';
        self::loging(self::$logPath, $str);
    }

    /**
     * Добавление лога в произвольный файл
     * Фиксируется время, ip, логин в системе
     * @param string $fileName абсолютный путь и имя файла лога
     * @param string $str строка для записи
     */
    public static function loging($fileName, $str) {
        $fp=fopen($fileName, "a");
        if ($fp=fopen($fileName, "a")) {
            //OK. Work with file
            $s = date("Y.m.d H:i:s")." ip:".Logger::getUserIp();
            if (is_array($str) || is_object($str)) $str = print_r($str, true);
            $s .= " ".$str."\r\n";
            fwrite($fp, $s);
            fclose($fp);
        }
    }

    public static function getUserIp() {
        $ip = null;
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER["HTTP_X_REAL_IP"];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'127.0.0.1';
        }
        return $ip;
    }
}