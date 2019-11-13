<?php
/**
 * Контроллер для подтверждения получения статусов
 *
 */
namespace App\Controllers;



use App\Helpers\AuthHelper;
use App\Helpers\ResponseContainer;
use App\Models\OrderModel;

class CommitlaststatusController extends BaseController
{
    public function run($xml)
    {
        $clientId = AuthHelper::getUserData()['code'];

        $result = OrderModel::commitOrdersByClientId($clientId);

        if($result){
            ResponseContainer::setMainTagValue('OK');
            ResponseContainer::addMainTagAttribute([
                'error' => 0
            ]);
        }
    }
}