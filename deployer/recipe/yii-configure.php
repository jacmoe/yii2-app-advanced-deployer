<?php
// This is a modified version of
// https://github.com/deployphp/recipes/blob/master/recipes/configure.php

/**
 * Make shared_dirs and configure files from templates
 */
task('deploy:configure', function () {

    /**
     * Paser value for template compiler
     *
     * @param array $matches
     * @return string
     */
    $paser = function($matches) {
        if (isset($matches[1])) {
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
    $releaseDir = env('release_path');

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
