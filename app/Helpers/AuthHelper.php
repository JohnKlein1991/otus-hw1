<?php


namespace App\Helpers;


use App\Models\ClientModel;

class AuthHelper
{
    //данные авторизиванного пользователя
    private static $userData = [];
    /**
     *
     *
     * @param $xml \SimpleXMLElement
     * @return bool
     */
    static public function checkAuth($xml)
    {
        if(!$xml->auth) return false;

        $login = (string) $xml->auth->attributes()->login;
        $password = (string) $xml->auth->attributes()->pass;

        if(!$login || !$password) return false;

        $user = ClientModel::where('login', $login);
        if($user->value('pass') === $password){
            self::$userData['code'] = $user->value('code');
            self::$userData['company'] = $user->value('company');
//            self::$userData['name'] = $user->value('name');
            return true;
        } else {
            return false;
        }
    }

    static public function getUserData()
    {
        return self::$userData;
    }

    /**
     * Возвращает code (id) пользователя, чьи доступы пришли в запросе
     *
     * @return integer|null
     */
    static public function getUserCode()
    {
        return self::$userData['code'] ?? null;
    }
}