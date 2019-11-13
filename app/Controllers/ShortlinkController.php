<?php
/**
 * Контроллер для запроса для генерации коротких ссылок
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class ShortlinkController extends BaseController
{
    public function run($xml)
    {
        if(!$this->validateXml($xml)) return;

        $data = $this->getDataFromXml($xml);
        $data['hash'] = $this->createHash();

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getShortLink($data), true);

        if($result['success']){
            $type = strval($xml->link->attributes()->short);

            if($type === '1') {
                if(isset($result['hash'])) {
                    echo $data['hash'];
                    exit;
                }
            } else {
                ResponseContainer::addAttachment('hash', $data['hash']);
            }
        } else {
            ResponseContainer::addAttachment('error', '', [
                'error' => isset($result['errorNumber']) ? $result['errorNumber'] : '',
                'errormsg' => isset($result['error']) ? $result['error'] : '',
            ]);
        }
    }

    /**
     * Получает нужные данные из xml и возвращает их в виде массива
     *
     * @param $xml SimpleXMLElement
     * @return array
     */
    private function getDataFromXml($xml)
    {
        $result['link'] = strval($xml->link);

        return $result;
    }

    /**
     * Проверяет валидность xml для данного вида запроса (наличие обязательных полей)
     *
     * @param $xml SimpleXMLElement
     * @return bool
     */
    private function validateXml($xml)
    {
        if(!strval($xml->link)) {
            ResponseContainer::setMainTagValue('field [link] cannot be empty');
            ResponseContainer::addMainTagAttribute('error', 49);
            return false;
        }
        return true;
    }

    /**
     * Создает хэш для короткой ссылки
     *
     */
    private function createHash()
    {
        $str = "QqWwEeRrTtYyUuIiOoPpAaSsDdFfGgHhJjKkLlZzXxCcVvBbNnMm1234567890";
        return substr(str_shuffle($str), 0, 8);
    }
}