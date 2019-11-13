<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/bootstrap.php';

use App\Helpers\ResponseContainer;
use App\Helpers\Logger;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/xml; charset=UTF-8");
//header("Content-Type: application/json; charset=UTF-8");

$data = file_get_contents('php://input');

Logger::logSetup(ROOT . '/app/Logs/apipostsrvs_'.date('Ymd'));
Logger::log('______START______');
Logger::log('Входящие данные:');
Logger::log($data);

$requestsWithoutAuth = [
    'tracking',
    'tracking17',
    'townlist',
    'regionlist',
    'streetlist',
    'services',
    'shortlink'
];

try {
    $xml = new SimpleXMLElement($data);

    if(!$xml) {
        ResponseContainer::addAttachment('error', '', [
            'errormsg' => 'Некоректный запрос'
        ]);
        ResponseContainer::setMainTagName('request');
    } elseif (!App\Helpers\AuthHelper::checkAuth($xml) && !in_array($xml->getName(), $requestsWithoutAuth)){
        ResponseContainer::addAttachment('error', '', [
            'error' => '1',
            'errormsg' => 'authorization error'
        ]);
        ResponseContainer::setMainTagName('request');
    } else {
        $requestName = $xml->getName();
        $className = 'App\Controllers\\' . ucfirst($requestName) . 'Controller';
        if (!class_exists($className)) {
            ResponseContainer::addAttachment('error', 'Некоректный запрос');
            ResponseContainer::setMainTagName('request');
        } else {
            Logger::log('Контроллер: '.$className);
            ResponseContainer::setMainTagName($requestName);
            $controller = new $className($xml);
            $result = $controller->run($xml);
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
    if(strpos($e->getMessage(), 'could not be parsed as XML') !== false){
        ResponseContainer::addAttachment('error', 'Syntax error');
    } else {
        ResponseContainer::addAttachment('error', 'Произошла ошибка');
    }
    ResponseContainer::setMainTagName('request');

    Logger::log('Ошибка: ' . $e->getMessage());
}

if(ResponseContainer::$readyResponse) {
    $response = ResponseContainer::$readyResponse;
} else {
    $response = ResponseContainer::getResponseInXML();
}
$responseText = $response->asXML();
Logger::log('Ответ: '. $responseText);
echo $responseText;
Logger::log('______END______');