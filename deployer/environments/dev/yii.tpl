#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require('{{release_path}}/vendor/autoload.php');
require('{{release_path}}/vendor/yiisoft/yii2/Yii.php');
require('{{release_path}}/common/config/bootstrap.php');
require('{{release_path}}/console/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require('{{release_path}}/common/config/main.php'),
    require('{{release_path}}/common/config/main-local.php'),
    require('{{release_path}}/console/config/main.php'),
    require('{{release_path}}/console/config/main-local.php')
);

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
