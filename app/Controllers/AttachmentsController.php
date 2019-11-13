<?php
/**
 * Контроллер для получения вложений к накладной
 *
 */
namespace App\Controllers;


use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;

class AttachmentsController extends BaseController
{
    public function run($xml)
    {
        $orderno = (string) $xml->orderno;

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getAttachments($orderno), true);

        if($result['success']){
            foreach ($result['attachments'] as $item) {
                ResponseContainer::addAttachment('item', $item['value'], [
                    'name' => $item['name'],
                    'size' => $item['size']
                ]);
            }
        } else {
            ResponseContainer::addAttachment('error', '', [
                'error' => isset($result['errorNumber']) ? $result['errorNumber'] : '',
                'errormsg' => isset($result['error']) ? $result['error'] : '',
            ]);
        }
    }
}