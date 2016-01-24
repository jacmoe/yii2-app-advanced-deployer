<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host={{app.mysql.host}};dbname={{app.mysql.dbname}}',
            'username' => '{{app.mysql.username}}',
            'password' => '{{app.mysql.password}}',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
        ],
    ],
];
