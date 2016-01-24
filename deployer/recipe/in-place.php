<?php
/* (c) Jacob Moen <jacmoe.dk@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

require_once __DIR__ . '/common.php';

/**
* Recipe for in place pseudo-release - does not create any release folders
*/

  /**
  * Run migrations
  */
  task('inplace:run_migrations', function () {
    run('php {{deploy_path}}/yii migrate up --interactive=0');
  })->desc('Run migrations');

  /**
  * Installing vendors tasks - uses deploy path instead of release path.
  */
  task('inplace:vendors', function () {
    if (commandExist('composer')) {
      $composer = 'composer';
    } else {
      run("cd {{deploy_path}} && curl -sS https://getcomposer.org/installer | php");
      $composer = 'php composer.phar';
    }

    $composerEnvVars = env('env_vars') ? 'export ' . env('env_vars') . ' &&' : '';
    run("cd {{deploy_path}} && $composerEnvVars $composer {{composer_options}}");

  })->desc('Installing vendors');


  task('inplace:configure', function () {

    /**
    * Paser value for template compiler
    *
    * @param array $matches
    * @return string
    */
    $paser = function($matches) {
      if (isset($matches[1])) {
        if($matches[1] === 'release_path') {
          $matches[1] = 'deploy_path';
        }
        $value = env()->get($matches[1]);
        if (is_null($value) || is_bool($value) || is_array($value)) {
          $value = var_export($value, true);
        }
      } else {
        $value = $matches[0];
      }

      return $value;
    };

    /**
    * Template compiler
    *
    * @param string $contents
    * @return string
    */
    $compiler = function ($contents) use ($paser) {
      $contents = preg_replace_callback('/\{\{\s*([\w\.]+)\s*\}\}/', $paser, $contents);

      return $contents;
    };

    $configFiles = '/deployer/environments/prod';
    $stage = env('app.stage');
    if($stage == 'dev') {
      $configFiles = '/deployer/environments/dev';
    }


    $finder   = new \Symfony\Component\Finder\Finder();
    $iterator = $finder
    ->ignoreDotFiles(false)
    ->files()
    ->name('/\.tpl$/')
    ->in(getcwd() . $configFiles);

    $tmpDir = sys_get_temp_dir();
    $releaseDir = env('deploy_path');

    /* @var $file \Symfony\Component\Finder\SplFileInfo */
    foreach ($iterator as $file) {
      $success = false;
      // Make tmp file
      $tmpFile = tempnam($tmpDir, 'tmp');
      if (!empty($tmpFile)) {
        try {
          $contents = $compiler($file->getContents());

          // cookie validation keys
          if(basename($file) === 'main-local.php.tpl') {
            $length = 32;
            $bytes = openssl_random_pseudo_bytes($length);
            $key = strtr(substr(base64_encode($bytes), 0, $length), '+/=', '_-.');
            $contents = preg_replace('/(("|\')cookieValidationKey("|\')\s*=>\s*)(""|\'\')/', "\\1'$key'", $contents);
          }

          $target   = preg_replace('/\.tpl$/', '', $file->getRelativePathname());
          // Put contents and upload tmp file to server
          if (file_put_contents($tmpFile, $contents) > 0) {
            if(basename($file) === 'yii.tpl') {
              upload($tmpFile, "$releaseDir/" . $target);
              run('chmod +x ' . "$releaseDir/" . $target);
            } else {
              run("mkdir -p $releaseDir/" . dirname($target));
              upload($tmpFile, "$releaseDir/" . $target);
            }
            $success = true;
          }
        } catch (\Exception $e) {
          $success = false;
        }
        // Delete tmp file
        unlink($tmpFile);
      }
      if ($success) {
        writeln(sprintf("<info>✔</info> %s", $file->getRelativePathname()));
      } else {
        writeln(sprintf("<fg=red>✘</fg=red> %s", $file->getRelativePathname()));
      }
    }
  })->desc('Make configure files for your stage');

  /**
   * Make writable dirs.
   */
  task('inplace:writable', function () {
      $dirs = join(' ', get('writable_dirs'));
      $sudo = get('writable_use_sudo') ? 'sudo' : '';
      $httpUser = get('http_user');

      if (!empty($dirs)) {
          try {
              if (null === $httpUser) {
                  $httpUser = run("ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1")->toString();
              }

              $releaseDir = env('deploy_path');
              cd($releaseDir);

              if (strpos(run("chmod 2>&1; true"), '+a') !== false) {
                  if (!empty($httpUser)) {
                      run("$sudo chmod +a \"$httpUser allow delete,write,append,file_inherit,directory_inherit\" $dirs");
                  }

                  run("$sudo chmod +a \"`whoami` allow delete,write,append,file_inherit,directory_inherit\" $dirs");
              } elseif (commandExist('setfacl')) {
                  if (!empty($httpUser)) {
                      if (!empty($sudo)) {
                          run("$sudo setfacl -R -m u:\"$httpUser\":rwX -m u:`whoami`:rwX $dirs");
                          run("$sudo setfacl -dR -m u:\"$httpUser\":rwX -m u:`whoami`:rwX $dirs");
                      } else {
                          // When running without sudo, exception may be thrown
                          // if executing setfacl on files created by http user (in directory that has been setfacl before).
                          // These directories/files should be skipped.
                          // Now, we will check each directory for ACL and only setfacl for which has not been set before.
                          $writeableDirs = get('writable_dirs');
                          foreach ($writeableDirs as $dir) {
                              // Check if ACL has been set or not
                              $hasfacl = run("getfacl -p $dir | grep \"^user:$httpUser:.*w\" | wc -l")->toString();
                              // Set ACL for directory if it has not been set before
                              if (!$hasfacl) {
                                  run("setfacl -R -m u:\"$httpUser\":rwX -m u:`whoami`:rwX $dir");
                                  run("setfacl -dR -m u:\"$httpUser\":rwX -m u:`whoami`:rwX $dir");
                              }
                          }
                      }
                  } else {
                      run("$sudo chmod 777 -R $dirs");
                  }
              } else {
                  run("$sudo chmod 777 -R $dirs");
              }
          } catch (\RuntimeException $e) {
              $formatter = \Deployer\Deployer::get()->getHelper('formatter');

              $errorMessage = [
                  "Unable to setup correct permissions for writable dirs.                  ",
                  "You need to configure sudo's sudoers files to not prompt for password,",
                  "or setup correct permissions manually.                                  ",
              ];
              write($formatter->formatBlock($errorMessage, 'error', true));

              throw $e;
          }
      }

  })->desc('Make writable dirs');

  /**
  * Main task
  */
  task('inplace', [
    'inplace:vendors',
    'inplace:writable',
    'inplace:configure'
    ])->desc('Deploy your project in place');

    after('inplace', 'success');
