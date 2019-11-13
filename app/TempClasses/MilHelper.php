<?php
/**
 * Временная прослойка для работы с БД Милевского (в дальнейшем, будет заменена более подходящими вещами)))
 */

namespace App\TempClasses;

use PDO;

class MilHelper
{
    private $milDB;

    public function __construct()
    {
        $conf = require_once __DIR__ . '/config.php';

        $milHost = $conf['MIL_DB_HOST'];
        $milUser = $conf['MIL_DB_USER'];
        $milPass = $conf['MIL_DB_PASSWORD'];
        $milDBName = $conf['MIL_DB_NAME'];

        $this->milDB = new PDO("mysql:host=$milHost;dbname=$milDBName;charset=utf8;", $milUser, $milPass);
    }

    public function getOrdersListByDates($dateFrom, $dateTo, $clientCode, $addData = [])
    {
        $sql = "select * from address 
                where ldtime between '{$dateFrom}' and '{$dateTo} 23:59:59'
                 and client_id like '{$clientCode}%'";
        $orders = $this->milDB->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $result = [
            'orders' => $orders,
            'success' => true
        ];
        return json_encode($result);
    }

    public function getOrdersListByIds($arr)
    {
        $result1 = [
            'success' => true,
            'orders' => []
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function chandeOrderStatus($data)
    {
        $result1 = [
            'success' => true,
            'Состояние' => 'Данные успешно записаны'
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getAttachments($orderno)
    {
        $result1 = [
            'success' => true,
            'attachments' => [
                [
                    'name' => 'doc1.docx',
                    'size' => '35654',
                    'value' => 'JVBERi0xLjMN
                  JUBQREYwMTIzNDU2Nzg5IDI NMyAwIG9iag08PA0vVHlwZSA
                  U3VidHlwZSAvSW1hZ2UNL1d pZHRoIDE4MDgNL0hlaWdodCA
                  ggNCAwIFINL0JpdHNQZXJDb 21wb25lbnQgMQ0vRGVjb2RlU
                  ENL0NvbHVtbnMgMTgwOA0+P g0vSW1hZ2VNYXNrIHRydWUNL
                  XhEZWNvZGUNPj4Nc3RyZWFt DQ',
                ],
                [
                    'name' => 'photo2.jpg',
                    'size' => '74861',
                    'value' => 'VBERi0xLjMN 
                  JUBQREYwMTIzNDU2Nzg5IDI NMyAwIG9iag08PA0vVHlwZSA
                  vWE9iamVjdA0vU3VidHlwZS AvSW1hZ2UNL1dpZHRoIDEzNj 
                  gNL0hlaWdodCAxMzMzDS9MZ W5ndGggNCAwIFINL0JpdHNQZ 
                  XJDb21wb25lbnQgMQ0vRGVj b2RlUGFybXMgPDwNL0sgLTEN 
                  L0NvbHVtbnMgMTM2OA0+Pg0 vSW',
                ],
            ]
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getTrackingByOrderno($orderno)
    {
        $tracking = [
            'sender' => [
                'value' => [
                    [
                        'town' => [
                            'attr' => [
                                'code' => 1,
                                'country' => 'RU'
                            ],
                            'value' => 'Москва город'
                        ],
                    ]
                ],
            ],
            'receiver' => [
                'value' => [
                    [
                        'town' => [
                            'attr' => [
                                'code' => 1,
                                'country' => 'RU'
                            ],
                            'value' => 'Москва город'
                        ],
                    ],
                    [
                        'date' => [
                            'value' => '2015-04-18'
                        ]
                    ]
                ]
            ],
            'AWB' => [
                'value' => 'Barcode'
            ],
            'weight' => [
                'value' => '0'
            ],
            'quantity' => [
                'value' => '1'
            ],
            'currcoords' => [
                'attr' => [
                    'lat' => '',
                    'lon' => '',
                    'accuracy' => '',
                    'RequestDateTime' => '',
                ]
            ],
            'status' => [
                'attr' => [
                    'lat' => '',
                    'lon' => '',
                    'accuracy' => '',
                    'RequestDateTime' => '',
                ],
                'value' => 'COMPLETE'
            ],
            'statushistory' => [
                'value' => [
                    [
                        'status' => [
                            'value' => 'NEW',
                            'attr' => [
                                'eventstore' => 'Офис в Москве',
                                'eventtime' => '2016-05-30 10:20:00',
                                'createtimegmt' => '2016-06-03 16:14:44',
                                'message' => '',
                                'title' => 'Новый',
                                'country' => 'RU',
                            ]
                        ]
                    ],
                    [
                        'status' => [
                            'value' => 'NEW',
                            'attr' => [
                                'eventstore' => 'Офис в Москве',
                                'eventtime' => '2016-05-30 10:20:00',
                                'createtimegmt' => '2016-06-03 16:14:44',
                                'message' => '',
                                'title' => 'Новый',
                                'country' => 'RU',
                            ]
                        ]
                    ],
                    [
                        'status' => [
                            'value' => 'NEW',
                            'attr' => [
                                'eventstore' => 'Офис в Москве',
                                'eventtime' => '2016-05-30 10:20:00',
                                'createtimegmt' => '2016-06-03 16:14:44',
                                'message' => '',
                                'title' => 'Новый',
                                'country' => 'RU',
                            ]
                        ]
                    ],
                    [
                        'status' => [
                            'value' => 'NEW',
                            'attr' => [
                                'eventstore' => 'Офис в Москве',
                                'eventtime' => '2016-05-30 10:20:00',
                                'createtimegmt' => '2016-06-03 16:14:44',
                                'message' => '',
                                'title' => 'Новый',
                                'country' => 'RU',
                            ]
                        ]
                    ],
                    [
                        'status' => [
                            'value' => 'NEW',
                            'attr' => [
                                'eventstore' => 'Офис в Москве',
                                'eventtime' => '2016-05-30 10:20:00',
                                'createtimegmt' => '2016-06-03 16:14:44',
                                'message' => '',
                                'title' => 'Новый',
                                'country' => 'RU',
                            ]
                        ]
                    ],
                ]
            ],
        ];

        $result1 = [
            'success' => true,
            'tracking' => $tracking
        ];
        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getWaybillByData($data)
    {
        $content = "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OTggMzA1IDQ3MSA1MjUgMjI5IDIzOSA0NTUgMjI5IDc5OSA1MjUgNTI3IDUyNSA1MjUgMzQ5IDM5MSAzMzUgNTI1IDQ1MiA3MTUgNDMzIDQ1MyAzOTUgMzE0IDQ2MCAzMTQgNDk4IF0KIDE2MCBbIDIyNiAzMjYgNDk4IDUwNyA0OTggNTA3IDQ5OCA0OTggMzkzIDgzNCA0MDIgNTEyIDQ5OCAzMDYgNTA3IDM5NCAzMzkgNDk4IDMzNiAzMzQgMjkyIDU1MCA1ODYgMjUyIDMwNyAyNDYgNDIyIDUxMiA2MzYgNjcxIDY3NSA0NjMgXQogMTkyIDE5NyA1NzkgMTk4IFsgNzYzIDUzMyBdCiAyMDAgMjAzIDQ4OCAyMDQgMjA3IDI1MiAyMDggWyA2MjUgNjQ2IF0KIDIxMCAyMTQgNjYyIDIxNSBbIDQ5OCA2NjQgXQogMjE3IDIyMCA2NDIgMjIxIFsgNDg3IDUxNyA1MjcgXQogMjI0IDIyOSA0NzkgMjMwIFsgNzczIDQyMyBdCiAyMzIgMjM1IDQ5OCAyMzYgMjM5IDIyOSAyNDAgMjQxIDUyNSAyNDIgMjQ2IDUyNyAyNDcgWyA0OTggNTI5IF0KIDI0OSAyNTIgNTI1IDI1MyBbIDQ1MyA1MjUgNDUzIF0KIDEwNDAgWyA1NzkgNTM4IF0KIDEwNDMgWyA0MzAgNjQ0IF0KIDEwNDcgWyA0NzQgNjQyIF0KIDEwNTAgWyA1NDMgNjExIDg1NSA2MjMgNjYyIDYyMiBdCiAxMDU3IFsgNTMzIDQ4NyBdCiAxMDYwIDEwNjAgNjk3IDEwNjQgMTA2NCA4NjggMTA3MCAxMDcwIDg3OSAxMDcyIFsgNDc5IDUzMyA0NzkgMzQ2IDU1OCA0OTggNjg5IDQyMyA1NDEgXQogMTA4MiBbIDQ2NCA1MTAgNjc2IDUzNSA1MjcgNTIxIDUyNSA0MjMgMzg3IDQ1MyBdCiAxMDk1IDEwOTUgNDY5IDEwOTcgMTA5NyA3NDkgMTEwMCBbIDQ3MCA0NDMgXQogMTEwMyAxMTAzIDQ3NCBdCi9DSURUb0dJRE1hcCAxMCAwIFIKPj4KZW5kb2JqCjcgMCBvYmoKPDwvTGVuZ3RoIDM0NT4+CnN0cmVhbQovQ0lESW5pdCAvUHJvY1NldCBmaW5kcmVzb3VyY2Ug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MzEyIDQ5OCA0OTggMjU4IDMwNiAyNjcgNDMwIF0KIDQ4IDU3IDUwNyA1OCA1OSAyNzYgNjAgNjIgNDk4IDYzIFsgNDYzIDg5OCA2MDYgNTYxIDUyOSA2MzAgNDg4IDQ1OSA2MzcgNjMxIDI2NyAzMzEgNTQ3IDQyMyA4NzQgNjU5IDY3NiA1MzIgNjg2IDU2MyA0NzMgNDk1IDY1MyA1OTEgOTA2IDU1MSA1MjAgNDc4IDMyNSA0MzAgMzI1IDQ5OCA0OTggMzAwIDQ5NCA1MzcgNDE4IDUzNyA1MDMgMzE2IDQ3NCA1MzcgMjQ2IDI1NSA0ODAgMjQ2IDgxMyA1MzcgNTM4IDUzNyA1MzcgMzU1IDM5OSAzNDcgNTM3IDQ3MyA3NDUgNDU5IDQ3NCAzOTcgMzQ0IDQ3NSAzNDQgNDk4IF0KIDE2MCBbIDIyNiAzMjYgNDk4IDUwNyA0OTggNTA3IDQ5OCA0OTggNDE1IDgzNCA0MTYgNTM5IDQ5OCAzMDYgNTA3IDM5MCAzNDIgNDk4IDMzOCAzMzYgMzAxIDU2MyA1OTggMjY4IDMwMyAyNTIgNDM1IDUzOSA2NTggNjkxIDcwMiA0NjMgXQogMTkyIDE5NyA2MDYgMTk4IFsgNzc1IDUyOSBdCiAyMDAgMjAzIDQ4OCAyMDQgMjA3IDI2NyAyMDggWyA2MzkgNjU5IF0KIDIxMCAyMTQgNjc2IDIxNSBbIDQ5OCA2ODEgXQogMjE3IDIyMCA2NTMgMjIxIFsgNTIwIDUzMiA1NTUgXQogMjI0IDIyOSA0OTQgMjMwIFsgNzc1IDQxOCBdCiAyMzIgMjM1IDUwMyAyMzYgMjM5IDI0NiAyNDAgMjQxIDUzNyAyNDIgMjQ2IDUzOCAyNDcgWyA0OTggNTQ0IF0KIDI0OSAyNTIgNTM3IDI1MyBbIDQ3NCA1MzcgNDc0IF0KIDEwNDAgMTA0MCA2MDYgMTA0MyBbIDQzMiA2NzMgXQogMTA0NyBbIDQ4MiA2NTIgXQogMTA1MiAxMDUyIDg3NCAxMDU0IFsgNjc2IDYzMCBdCiAxMDU3IFsgNTI5IDQ5NSBdCiAxMDYyIDEwNjIgNjU4IDEwNzIgMTA3MiA0OTQgMTA3NiBbIDU4MiA1MDMgXQogMTA3OSBbIDQyNyA1NTcgNTU3IDQ5NSA1MjYgNzEzIDU0NSA1MzggNTM0IDUzNyA0MTggMzkwIDQ3NCBdCiAxMDk1IDEwOTUgNDg4IDExMDAgMTEwMCA0OTUgODQ3MCA4NDcwIDEwNDYgXQovQ0lEVG9HSURNYXAgMTcgMCBSCj4+CmVuZG9iagoxNCAwIG9iago8PC9MZW5ndGggMzQ1Pj4Kc3RyZWFtCi9DSURJbml0IC9Qcm9jU2V0IGZpbmRyZXNvdXJjZSBiZWdpbgoxMiBkaWN0IGJlZ2luCmJlZ2luY21hcAovQ0lEU3lzdGVtSW5mbwo8PC9SZWdpc3RyeSAoQWRvYmUpCi9PcmRlcmluZyAoVUNTKQovU3VwcGxlbWVudCAwCj4+IGRlZgovQ01hcE5hbWUgL0Fkb2JlLUlkZW50aXR5LVVDUyBkZWYKL0NNYXBUeXBlIDIgZGVmCjEgYmVnaW5jb2Rlc3BhY2VyYW5nZQo8MDAwMD4gPEZGRkY+CmVuZGNvZGVzcGFjZXJhbmdlCjEgYmVnaW5iZnJhbmdlCjwwMDAwPiA8RkZGRj4gPDAwMDA+CmVuZGJmcmFuZ2UKZW5kY21hcApDTWFwTmFtZSBjdXJyZW50ZGljdCAvQ01hcCBkZWZpbmVyZXNvdXJjZSBwb3AKZW5kCmVuZAplbmRzdHJlYW0KZW5kb2JqCjE1IDAgb2JqCjw8L1JlZ2lzdHJ5IChBZG9iZSkKL09yZGVyaW5nIChVQ1MpCi9TdXBwbGVtZW50IDAKPj4KZW5kb2JqCjE2IDAgb2JqCjw8L1R5cGUgL0ZvbnREZXNjcmlwdG9yCi9Gb250TmFtZSAvTVBERkFBK0NhbGlicmktQm9sZAogL0FzY2VudCA3NTAKIC9EZXNjZW50IC0yNTAKIC9DYXBIZWlnaHQgNjMyCiAvRmxhZ3MgMjYyMTQ4CiAvRm9udEJCb3ggWy01MTkgLTM0OSAxMjYzIDEwMzldCiAvSXRhbGljQW5nbGUgMAogL1N0ZW1WIDE2NQogL01pc3NpbmdXaWR0aCA1MDcKL0ZvbnRGaWxlMiAxOCAwIFIKPj4KZW5kb2JqCjE3IDAgb2JqCjw8L0xlbmd0aCAyNjEKL0ZpbHRlciAvRmxhdGVEZWNvZGUKPj4Kc3RyZWFtCnic7c/ZSgNBEAXQGxPNpiZmd82iZv3//8sQhuCDAz4IgXAOdF2o7mq6k0q16q0f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MyAxMyAwIDMyIFsgMjI2IDMyNiA0MDEgNDk4IDUwNyA3MTUgNjgyIDIyMSAzMDMgMzAzIDQ5OCA0OTggMjUwIDMwNiAyNTIgMzg4IF0KIDQ4IDU3IDUwNyA1OCA1OSAyNjggNjAgNjIgNDk4IDYzIFsgNDYzIDg5NCA1NzkgNTQ0IDUyMiA2MTUgNDg4IDQ1OSA2MzEgNjIzIDI1MiAzMTkgNTIwIDQyMCA4NTUgNjQ1IDY1NCA1MTcgNjY0IDU0MyA0NTIgNDg3IDY0MiA1NjcgODkwIDUxOSA0ODcgNDY4IDMwNyAzODQgMzA3IDQ5OCA0OTggMjkxIDUxNCA1MTQgNDE2IDUxNCA0NzggMzA1IDUxNCA1MTQgMjI5IDIzOSA0NTUgMjI5IDc5MSA1MTQgNTEzIDUxNCA1MTQgMzQzIDM4OSAzMzUgNTE0IDQ0NiA3MTUgNDMzIDQ0NyAzOTUgMzE0IDQ2MCAzMTQgNDk4IF0KIDE2MCBbIDIyNiAzMjYgNDk4IDUwNyA0OTggNTA3IDQ5OCA0OTggMzkzIDgzNCA0MzEgNTEyIDQ5OCAzMDYgNTA3IDM5NCAzMzkgNDk4IDMzNiAzMzQgMjkyIDUzOCA1ODYgMjUyIDMwNyAyNDYgNDIyIDUxMiA2MzYgNjcxIDY3NSA0NjMgXQogMTkyIDE5NyA1NzkgMTk4IFsgNzYzIDUyMiBdCiAyMDAgMjAzIDQ4OCAyMDQgMjA3IDI1MiAyMDggWyA2MjUgNjQ1IF0KIDIxMCAyMTQgNjU0IDIxNSBbIDQ5OCA2NTggXQogMjE3IDIyMCA2NDIgMjIxIFsgNDg3IDUxNyA1MjcgXQogMjI0IDIyOSA1MTQgMjMwIFsgNzU0IDQxNiBdCiAyMzIgMjM1IDQ3OCAyMzYgMjM5IDIyOSAyNDAgWyA1MjUgNTE0IF0KIDI0MiAyNDYgNTEzIDI0NyBbIDQ5OCA1MjkgXQogMjQ5IDI1MiA1MTQgMjUzIFsgNDQ3IDUxNCA0NDcgXQogMTA0NCAxMDQ0IDY0NCAxMDc3IDEwNzcgNDc4IDEwODEgMTA4MSA1MTQgMTA4MyAxMDgzIDUxMCBdCi9DSURUb0dJRE1hcCAyNCAwIFIKPj4KZW5kb2JqCjIxIDAgb2JqCjw8L0xlbmd0aCAzNDU+PgpzdHJlYW0KL0NJRElu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";
        $result1 = [
            'success' => true,
            'content' => $content
        ];
        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function cancelOrder($data)
    {
        $result1 = [
            'success' => true,
        ];
        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getTownList($data)
    {
        $result1 = [
            'success' => true,
            'page' => '3',
            'totalcount' => '3',
            'totalpages' => '1',
            'towns' => [
                [
                    'code' => 234234,
                    'city' => [
                        'code' => 9090,
                        'name' => 'Shitosransk'
                    ],
                    'name' => 'Peredino',
                    'fiascode' => '79da737a-603b-4c19-9b54-9114c96fb912',
                    'kladrcode' => '2300000700000',
                    'shortname' => 'per',
                    'typename' => 'name',
                ],
                [
                    'code' => 56756,
                    'city' => [
                        'code' => 2727,
                        'name' => 'Shitosransk'
                    ],
                    'name' => 'Peredino',
                    'fiascode' => '79da737a-603b-4c19-9b54-9114c96fb912',
                    'kladrcode' => '233452352345',
                    'shortname' => 'per',
                    'typename' => 'name',
                ]
            ]
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getRegionList($data)
    {
        $result1 = [
            'success' => true,
            'cities' => [
                [
                    'code' => 1,
                    'country' => [
                        'code' => 93,
                        'name' => 'Россия',
                        'id' => 643,
                        'ShortName1' => 'RU',
                        'ShortName2' => 'RUS'
                    ],
                    'name' => 'Чита-Бурятия'
                ],
                [
                    'code' => 3,
                    'country' => [
                        'code' => 923,
                        'name' => 'ПРоссия',
                        'id' => 6432,
                        'ShortName1' => 'RUs',
                        'ShortName2' => 'RUSss'
                    ],
                    'name' => 'Чита-Бурятия'
                ],
            ]
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getStreetList($data)
    {
        $result1 = [
            'success' => true,
            'page' => '3',
            'totalcount' => '3',
            'totalpages' => '1',
            'streetlist' => [
                [
                    'shortname' => 'Академика Хохлова',
                    'name' => 'Академика Хохлова sst',
                    'typename' => 'st'
                ],
                [
                    'shortname' => 'Академика Хохлова',
                    'name' => 'Академика Хохлова sst',
                    'typename' => 'st'
                ],
            ]
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getItemlist($data)
    {
        $result1 = [
            'success' => true,
            'page' => '3',
            'totalcount' => '3',
            'totalpages' => '1',
            'itemlist' => [
                [
                    'code' => 'Академика Хохлова',
                    'article' => 'Академика Хохлова sst',
                    'barcode' => 'st',
                    'name' => 'st',
                    'retprice' => 'st',
                    'purchprice' => 'st',
                    'weight' => 'st',
                    'length' => 'st',
                    'width' => 'st',
                    'height' => 'st',
                    'CountInPallet' => 'st',
                    'HasSerials' => 'st',
                    'CountryOfOrigin' => 'st',
                    'Message' => 'st',
                    'Message2' => 'st',
                    'quantity' => 'st',
                    'reserved' => 'st',
                ],
                [
                    'code' => 'Академика Хохлова',
                    'article' => 'Академика Хохлова sst',
                    'barcode' => 'st',
                    'name' => 'st',
                    'retprice' => 'st',
                    'purchprice' => 'st',
                    'weight' => 'st',
                    'length' => 'st',
                    'width' => 'st',
                    'height' => 'st',
                    'CountInPallet' => 'st',
                    'HasSerials' => 'st',
                    'CountryOfOrigin' => 'st',
                    'Message' => 'st',
                    'Message2' => 'st',
                    'quantity' => 'st',
                    'reserved' => 'st',
                ],
                [
                    'code' => 'Академика Хохлова',
                    'article' => 'Академика Хохлова sst',
                    'barcode' => 'st',
                    'name' => 'st',
                    'retprice' => 'st',
                    'purchprice' => 'st',
                    'weight' => 'st',
                    'length' => 'st',
                    'width' => 'st',
                    'height' => 'st',
                    'CountInPallet' => 'st',
                    'HasSerials' => 'st',
                    'CountryOfOrigin' => 'st',
                    'Message' => 'st',
                    'Message2' => 'st',
                    'quantity' => 'st',
                    'reserved' => 'st',
                ],
            ]
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getItemMovement($data)
    {
        $result1 = [
            'success' => true,
            'itemmovements' => [
                [
                    'code' => 234,
                    'date' => '2017-05-26',
                    'retprice' => 0,
                    'quantity' => 1,
                    'delivered' => 0,
                    'item' => [
                        'code' => 2343,
                        'name' => 'FooT kicker!!!'
                    ],
                    'status' => [
                        'code' => 3333,
                        'name' => 'Good ssi'
                    ],
                    'store' => [
                        'code' => 3212,
                        'name' => 'HZ o2'
                    ],
                    'order' => [
                        'ordercode' => 123123,
                        'number' => '123123-43',
                        'date' => '2017-05-24',
                        'orderno' => '14123',
                        'barcode' => '234000000234',
                        'company' => 'ТОВАР',
                        'address' => 'Кравченко ул., 1',
                        'delivereddate' => '2017-05-29',
                        'deliveredtime' => '12:00:00',
                    ],
                    'document' => [
                        'code' => 823838,
                        'number' => 233,
                        'date' => '2017-05-26',
                        'message' => 'affffee asdf groko'
                    ]
                ],
                [
                    'code' => 234,
                    'date' => '2017-05-26',
                    'retprice' => 0,
                    'quantity' => 1,
                    'delivered' => 0,
                    'item' => [
                        'code' => 2343,
                        'name' => 'FooT kicker!!!'
                    ],
                    'status' => [
                        'code' => 3333,
                        'name' => 'Good ssi'
                    ],
                    'store' => [
                        'code' => 3212,
                        'name' => 'HZ o2'
                    ],
                    'order' => [
                        'ordercode' => 123123,
                        'number' => '123123-43',
                        'date' => '2017-05-24',
                        'orderno' => '14123',
                        'barcode' => '234000000234',
                        'company' => 'ТОВАР',
                        'address' => 'Кравченко ул., 1',
                        'delivereddate' => '2017-05-29',
                        'deliveredtime' => '12:00:00',
                    ],
                    'document' => [
                        'code' => 823838,
                        'number' => 233,
                        'date' => '2017-05-26',
                        'message' => 'affffee asdf groko'
                    ]
                ]
            ]
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getPvzList($data)
    {
        $result1 = [
            'success' => true,
            'pvzlist' => [
                [
                    'code' => '123',
                    'clientcode' => '123',
                    'name' => '123',
                    'parentcode' => '123',
                    'parentname' => '123',
                    'town' => '123',
                    'address' => '123',
                    'phone' => '123',
                    'comment' => '123',
                    'worktime' => '123',
                    'traveldescription' => '123',
                    'maxweight' => '123',
                    'acceptcash' => '123',
                    'acceptcard' => '123',
                    'acceptfitting' => '123',
                    'acceptindividuals' => '123',
                    'latitude' => '123',
                    'longitude' => '123',
                ],
                [
                    'code' => '123',
                    'clientcode' => '123',
                    'name' => '123',
                    'parentcode' => '123',
                    'parentname' => '123',
                    'town' => '123',
                    'address' => '123',
                    'phone' => '123',
                    'comment' => '123',
                    'worktime' => '123',
                    'traveldescription' => '123',
                    'maxweight' => '123',
                    'acceptcash' => '123',
                    'acceptcard' => '123',
                    'acceptfitting' => '123',
                    'acceptindividuals' => '123',
                    'latitude' => '123',
                    'longitude' => '123',
                ]
            ]
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getServices()
    {
        $result1 = [
            'success' => true,
            'services' => [
                [
                    'code' => 1,
                    'name' => 'Эконом'
                ],
                [
                    'code' => 1,
                    'name' => 'Эконом'
                ],
                [
                    'code' => 1,
                    'name' => 'Эконом'
                ],
                [
                    'code' => 1,
                    'name' => 'Эконом'
                ],
                [
                    'code' => 1,
                    'name' => 'Эконом'
                ],
                [
                    'code' => 1,
                    'name' => 'Эконом'
                ],
                [
                    'code' => 1,
                    'name' => 'Эконом'
                ],
            ]
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }


    public function getCalc($data)
    {
        $result1 = [
            'success' => true,
            'calculator' => [
                [
                    'townfrom' => 'Moscow city',
                    'townto' => 'Ulan-Ude',
                    'mass' => '7.3',
                    'service' => 'Эконом',
                    'zone' => 2,
                    'price' => 1232,
                    'mindeliverydays' => 2,
                    'maxdeliverydays' => 4,
                ],
                [
                    'townfrom' => 'Moscow city',
                    'townto' => 'Ulan-Ude',
                    'mass' => '7.3',
                    'service' => 'Эконом',
                    'zone' => 2,
                    'price' => 1232,
                    'mindeliverydays' => 2,
                    'maxdeliverydays' => 4,
                ],
                [
                    'townfrom' => 'Moscow city',
                    'townto' => 'Ulan-Ude',
                    'mass' => '7.3',
                    'service' => 'Эконом',
                    'zone' => 2,
                    'price' => 1232,
                    'mindeliverydays' => 2,
                    'maxdeliverydays' => 4,
                ],
            ]
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getSmalist($data)
    {
        $result1 = [
            'success' => true,
            'smalist' => [
                [
                    'code' => 234,
                    'number' => 2342,
                    'actdate' => '2016-02-12',
                    'datepay' => '2016-02-12',
                    'dateto' => '2016-02-12',
                    'promiseddatepay' => '2016-02-12<',
                    'price' => '123',
                    'pricecorr' => '222',
                    'rur' => '43433',
                    'pricekur' => '23423',
                    'priceag' => '2343',
                    'payno' => '23423',
                    'paytype' => '4',
                    'paytypename' => 'Безнал',
                    'signedcopyreceived' => 'NO',
                ],
                [
                    'code' => 234,
                    'number' => 2342,
                    'actdate' => '2016-02-12',
                    'datepay' => '2016-02-12',
                    'dateto' => '2016-02-12',
                    'promiseddatepay' => '2016-02-12<',
                    'price' => '123',
                    'pricecorr' => '222',
                    'rur' => '43433',
                    'pricekur' => '23423',
                    'priceag' => '2343',
                    'payno' => '23423',
                    'paytype' => '4',
                    'paytypename' => 'Безнал',
                    'signedcopyreceived' => 'NO',
                ],
                [
                    'code' => 234,
                    'number' => 2342,
                    'actdate' => '2016-02-12',
                    'datepay' => '2016-02-12',
                    'dateto' => '2016-02-12',
                    'promiseddatepay' => '2016-02-12<',
                    'price' => '123',
                    'pricecorr' => '222',
                    'rur' => '43433',
                    'pricekur' => '23423',
                    'priceag' => '2343',
                    'payno' => '23423',
                    'paytype' => '4',
                    'paytypename' => 'Безнал',
                    'signedcopyreceived' => 'NO',
                ],
                [
                    'code' => 234,
                    'number' => 2342,
                    'actdate' => '2016-02-12',
                    'datepay' => '2016-02-12',
                    'dateto' => '2016-02-12',
                    'promiseddatepay' => '2016-02-12<',
                    'price' => '123',
                    'pricecorr' => '222',
                    'rur' => '43433',
                    'pricekur' => '23423',
                    'priceag' => '2343',
                    'payno' => '23423',
                    'paytype' => '4',
                    'paytypename' => 'Безнал',
                    'signedcopyreceived' => 'NO',
                ],
            ]
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getSmaDetail($data)
    {
        $result1 = [
            'success' => true,
            'smadetail' => [
                [
                    'code' => 234,
                    'ordercode' => 2342,
                    'orderno' => '2016-02-12',
                    'orderdate' => '2016-02-12',
                    'delivereddate' => '2016-02-12',
                    'company' => 'Nneh,j',
                    'price' => '222',
                    'rur' => '43433',
                    'inshprice' => '23423',
                    'pricekur' => '2343',
                    'priceag' => '23423',
                    'pricecalc' => '2342',
                    'paytype' => '2',
                    'paytypename' => 'наличными курьером',
                    'status' => 'Доставлено'
                ],
                [
                    'code' => 234,
                    'ordercode' => 2342,
                    'orderno' => '2016-02-12',
                    'orderdate' => '2016-02-12',
                    'delivereddate' => '2016-02-12',
                    'company' => 'Nneh,j',
                    'price' => '222',
                    'rur' => '43433',
                    'inshprice' => '23423',
                    'pricekur' => '2343',
                    'priceag' => '23423',
                    'pricecalc' => '2342',
                    'paytype' => '2',
                    'paytypename' => 'наличными курьером',
                    'status' => 'Доставлено'
                ],
            ]
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function getShortLink($data)
    {
        $result1 = [
            'success' => true,
            'hash' => '35AF350C'
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }

    public function createNewOrder($data)
    {
        $result1 = [
            'success' => true,
            'code' => '123',
            'orderno' => '123#test'
        ];

        $result2 = [
            'success' => false,
            'error' => 'Pshel v jzopa',
            'errorNumber' => 9
        ];

        return (rand(1,10) > 5) ? json_encode($result1) : json_encode($result2);
    }


}