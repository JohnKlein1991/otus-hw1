<?php
namespace App\Controllers;

abstract class BaseController
{
    public function __construct()
    {
        $corePath = getenv('CORE_PATH');

        require_once $corePath . '/models/core.php';
        require_once $corePath . '/classes/DB.php';
        require_once $corePath . '/models/couriers.php';
        require_once $corePath . '/models/orders.php';
        require_once $corePath . '/models/tablesRules.php';
        require_once $corePath . '/models/clients.php';
        require_once $corePath . '/models/givn.php';
        require_once $corePath . '/models/variables.php';
    }

    abstract public function run(\SimpleXMLElement $xml);
}