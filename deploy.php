<?php
require_once __DIR__ . '/deployer/recipe/yii2-app-advanced.php';
require_once __DIR__ . '/deployer/recipe/yii-configure.php';

if (!file_exists (__DIR__ . '/deployer/stage/servers.yml')) {
  die('Please create "' . __DIR__ . '/deployer/stage/servers.yml" before continuing.' . "\n");
}
serverList(__DIR__ . '/deployer/stage/servers.yml');
set('repository', '{{repository}}');

set('default_stage', 'production');

set('keep_releases', 2);

task('deploy:configure_composer', function () {
  $stage = env('app.stage');
  if($stage == 'dev') {
    env('composer_options', 'install --verbose --no-progress --no-interaction');
  }
})->desc('Configure composer');

before('deploy:vendors', 'deploy:configure_composer');
before('deploy:symlink', 'deploy:configure');
