<?php
require_once __DIR__ . '/deployer/recipe/yii2-app-advanced.php';
require_once __DIR__ . '/deployer/recipe/yii-configure.php';
require_once __DIR__ . '/deployer/recipe/in-place.php';

if (!file_exists (__DIR__ . '/deployer/stage/servers.yml')) {
  die('Please create "' . __DIR__ . '/deployer/stage/servers.yml" before continuing.' . "\n");
}
serverList(__DIR__ . '/deployer/stage/servers.yml');
set('repository', '{{repository}}');

set('default_stage', 'production');

set('keep_releases', 2);

set('writable_use_sudo', false); // Using sudo in writable commands?

task('deploy:configure_composer', function () {
  $stage = env('app.stage');
  if($stage == 'dev') {
    env('composer_options', 'install --verbose --no-progress --no-interaction');
  }
})->desc('Configure composer');

// uncomment the next two lines to run migrations
//after('deploy:shared', 'deploy:run_migrations');
//after('inplace:vendors', 'inplace:run_migrations');

before('deploy:vendors', 'deploy:configure_composer');
before('deploy:symlink', 'deploy:configure');
