<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require('{{release_path}}/vendor/autoload.php');
require('{{release_path}}/vendor/yiisoft/yii2/Yii.php');
require('{{release_path}}/common/config/bootstrap.php');
require('{{release_path}}/backend/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require('{{release_path}}/common/config/main.php'),
    require('{{release_path}}/common/config/main-local.php'),
    require('{{release_path}}/backend/config/main.php'),
    require('{{release_path}}/backend/config/main-local.php')
);

$application = new yii\web\Application($config);
$application->run();
