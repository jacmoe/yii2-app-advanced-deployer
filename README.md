Yii 2 Advanced Project Template
===============================

Yii 2 Advanced Project Template is a skeleton [Yii 2](http://www.yiiframework.com/) application best for
developing complex Web applications with multiple tiers.

The template includes three tiers: front end, back end, and console, each of which
is a separate Yii application.

The template is designed to work in a team development environment. It supports
deploying the application in different environments.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://poser.pugx.org/jacmoe/yii2-app-advanced-deployer/v/stable.png)](https://packagist.org/packages/jacmoe/yii2-app-advanced-deployer)
[![Total Downloads](https://poser.pugx.org/jacmoe/yii2-app-advanced-deployer/downloads.png)](https://packagist.org/packages/jacmoe/yii2-app-advanced-deployer)

HOW IS THIS DIFFERENT FROM STANDARD ADVANCED APP?
----------------------------------------------
* This project can be deployed by Deployer
* An `.htaccess` is added to the `frontend/web` and `backend/web` folders and *FollowSymlinks* is turned on.


DIRECTORY STRUCTURE
-------------------

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
deployer
    recipe/              contains deployer recipes
    stage/               contains deployer server configurations
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
tests                    contains various tests for the advanced application
    codeception/         contains tests developed with Codeception PHP Testing Framework
```

REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 5.4.0.

## Deployer

* [Download deployer.phar](http://deployer.org/deployer.phar)
~~~
mv deployer.phar /usr/local/bin/dep
chmod +x /usr/local/bin/dep
~~~
For more, see [Deployer - Installation](http://deployer.org/docs/installation)

## Local deployment
If you plan on using deployer to deploy locally, you need to install *openssh-server*:
~~~
sudo apt-get install openssh-server
~~~
That allows you to *ssh* into your local machine.


INSTALLATION
------------
## Install via Composer

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install this project template using the following command:

~~~
php composer.phar global require "fxp/composer-asset-plugin:~1.1.1"
php composer.phar create-project --prefer-dist --stability=dev jacmoe/yii2-app-advanced-deployer advanced
~~~

## Deployment

### servers.yml
First, create a file entitled `servers.yml` in the `deployer/stage` directory.  
You can copy the contents of `servers-sample.yml` to get you started.
### Create db on server
Prior to deployment, make sure that you have created a database on the server you want to deploy to.

### deploy command
When you have created a server configuration file, all you need to do is run this command:

~~~
dep deploy production
~~~
or
~~~
dep deploy local
~~~


CONFIGURATION
-------------
The configuration is handled automatically from the values you wrote in `servers.yml`.
