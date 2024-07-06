<?php

use yii\filters\Cors;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            //'cookieValidationKey' => Yii::$app->security->generateRandomString(),
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'enableSession' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'trace', 'warning', 'error'], // Уровень логирования, можно указать другие уровни, такие как 'trace', 'warning', 'error
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                'POST api/v1/register' => 'user/register',
                'POST api/v1/login' => 'user/login',
                'GET api/v1/logout' => 'user/logout',
                'GET api/v1/models' => 'model/index',
                'GET api/v1/models/<id:\d+>' => 'model/view',
                'GET api/v1/check-auth' => 'user/check-auth',
                'GET api/v1/scenarios' => 'scenario/index',
                'POST api/v1/scenarios' => 'scenario/create',
                'DELETE api/v1/scenarios/<id:\d+>' => 'scenario/delete',
                'PUT api/v1/scenarios/<id:\d+>' => 'scenario/update',
            ],
        ],
        'reCaptcha' => [
            'class' => 'himiklab\yii2\recaptcha\ReCaptchaConfig',
            'siteKey' => '6LcjPgYqAAAAACr4ePwNSFNq-GKm-9xHl9ccqd-k',
            'secret' => '6LcjPgYqAAAAAOy0rdIzCW1c2KkLkFTU_kMt4x4k'
        ],
        'session' => [
            'class' => 'yii\web\Session',
            'savePath' => '@runtime/sessions',
            'useCookies' => true,
            'cookieParams' => [
                'httpOnly' => false,
                'secure' => false,
                'sameSite' => \yii\web\Cookie::SAME_SITE_STRICT,
            ],
        ],
    ],

//    'as corsFilter' => [
//        'class' => Cors::class,
//        'cors' => [
//            'Origin' => ['http://localhost:5173'],
//            'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
//            'Access-Control-Allow-Credentials' => true,
//            'Access-Control-Max-Age' => 3600,
//            'Access-Control-Allow-Headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
//            'Access-Control-Expose-Headers' => [],
//        ],
//    ],
    'params' => $params,
//    'on beforeSend' => function ($event) {
//        $response = $event->sender;
//        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173');
//        $response->headers->set('Access-Control-Allow-Credentials', 'true');
//        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
//        $response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
//    },
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
