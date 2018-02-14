project-template-cakephp
========================

[![Build Status](https://travis-ci.org/QoboLtd/project-template-cakephp.svg?branch=master)](https://travis-ci.org/QoboLtd/project-template-cakephp)
[![Latest Stable Version](https://poser.pugx.org/qobo/project-template-cakephp/v/stable)](https://packagist.org/packages/qobo/project-template-cakephp)
[![Total Downloads](https://poser.pugx.org/qobo/project-template-cakephp/downloads)](https://packagist.org/packages/qobo/project-template-cakephp)
[![Latest Unstable Version](https://poser.pugx.org/qobo/project-template-cakephp/v/unstable)](https://packagist.org/packages/qobo/project-template-cakephp)
[![License](https://poser.pugx.org/qobo/project-template-cakephp/license)](https://packagist.org/packages/qobo/project-template-cakephp)
[![codecov](https://codecov.io/gh/QoboLtd/project-template-cakephp/branch/master/graph/badge.svg)](https://codecov.io/gh/QoboLtd/project-template-cakephp)

About
-----

This is a template for the new CakePHP projects.

Developed by [Qobo](https://www.qobo.biz), used in [Qobrix](https://qobrix.com).

Install
-------

When starting a new CakePHP project, do the following:

```bash
composer create-project qobo/project-template-cakephp example.com
cd example.com
git init
git add .
git commit -m "Initial commit"
./bin/build app:install DB_NAME=my_app,PROJECT_NAME="My Project",PROJECT_VERSION="v1.0.0"
```

Update
------

When you want to update your project with the latest
and greatest project-template, do the following:

```
cd exmample.com
git pull https://github.com/QoboLtd/project-template-cakephp
```


Usage
-----

### Quick

Now that you have the project template installed, check that it works
before you start working on your changes.  Fire up the PHP web server:

```
./bin/phpserv
```

Or run it on the alternative port:

```
./bin/phpserv -H localhost -p 9000
```

In your browser navigate to [http://localhost:8000](http://localhost:8000).
You should see the standard `phpinfo()` page.  If you do, all parts
are in place.


Now you can develop your PHP project as per usual, but with the following
advantages:

* Support for [PHP built-in web server](http://php.net/manual/en/features.commandline.webserver.php)
* Per-environment configuration using `.env` file, which is ignored by git
* Powerful build system ([Robo](http://robo.li/)) integrated
* Composer integrated with `vendor/` folder added to `.gitignore` .
* PHPUnit integrated with `tests/` folder and example unit tests.
* Sensible defaults for best practices - favicon.ico, robots.txt, MySQL dump, Nginx configuration, GPL, etc.

For example, you can easily automate the build process of your application
by modifying the included Robo files in `build/` folder.  Run the following
command to examine available targets:

```
./bin/build
```

As you can see, there are already some placeholders for your application's build
process.  By default, it is suggested that you have these:

* `app:install` - for installation process of your application,
* `app:update` - for the update process of the already installed application, and
* `app:remove` - for the application removal process and cleanup.

You can, of course, add your own, remove these, or change them any way you want.  Have a look at
[Robo](http://robo.li) documentation for more information on how
to use these targets and pass runtime configuration parameters.


Test
----

### PHPUnit

project-template brings quite a bit of setup for testing your projects.  The
first part of this setup is [PHPUnit](https://phpunit.de/).  To try it out,
runt the following command (don't worry if it fails, we'll get to it shortly):

```
./vendor/bin/phpunit
```

If it didn't work for you, here are some of the things to try:

* If `phpunit` command wasn't found, try `composer install` and then run the command again.  Chances are phpunit was removed during the `app:install`, which runs composer with `--no-dev` parameter.
* If you had some other issue, please [let us know](https://github.com/QoboLtd/project-template/issues/new).

### Travis CI

Continious Integration is a tool that helps to run your tests whenever you do any
changes on your code base (commit, merge, etc).  There are many tools that you can
use, but project-template provides an example integration with [Travis CI](https://travis-ci.org/).

Have a look at `.travis.yml` file, which describes the environment matrix, project installation
steps and ways to run the test suite.  For your real project, based on project-template, you'd probably
want to remove the example tests from the file.

### Examples

project-template provides a few examples of how to write and organize unit tests.  Have a look
in the `tests/` folder.  Now you have **NO EXCUSE** for not testing your applications!

###Configurations

####Analytics

Load Google Analytics in your project by doing the following.

```
<?= $this->element('Snippet/google_analytics', ['ua' => 'UA goes here']); ?>
```
The parameter is optional as it defaults to App.analytics configuration.

