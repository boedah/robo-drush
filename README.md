# Robo Drush Extension

Extension to execute Drush commands in [Robo](https://github.com/consolidation/robo).

[![Latest Stable Version](http://poser.pugx.org/boedah/robo-drush/v)](https://packagist.org/packages/boedah/robo-drush) [![Total Downloads](http://poser.pugx.org/boedah/robo-drush/downloads)](https://packagist.org/packages/boedah/robo-drush) [![License](http://poser.pugx.org/boedah/robo-drush/license)](https://packagist.org/packages/boedah/robo-drush) [![PHP Version Require](http://poser.pugx.org/boedah/robo-drush/require/php)](https://packagist.org/packages/boedah/robo-drush)

Runs Drush commands in a stack. You can define global options for all commands (like Drupal root and uri).

The option `-y` is assumed by default but can be overridden on calls to `exec()`
by passing `false` as the second parameter.

## Table of contents

- [Installation](#installation)
- [Testing](#testing)
- [Usage](#usage)
- [Examples](#examples)

## Installation

`composer require --dev boedah/robo-drush`

## Testing

`composer test`

## Usage

Use the trait (according to your used version) in your RoboFile:

```php
class RoboFile extends \Robo\Tasks
{
    use \Boedah\Robo\Task\Drush\Tasks;
}
```

## Examples

### Site update

This executes pending database updates and reverts all features (from code to database):

```php
$this->taskDrushStack()
    ->drupalRootDirectory('/var/www/html/some-site')
    ->uri('sub.example.com')
    ->maintenanceOn()
    ->updateDb()
    ->revertAllFeatures()
    ->maintenanceOff()
    ->run();
```

### Site install

```php
$this->taskDrushStack()
  ->siteName('Site Name')
  ->siteMail('site-mail@example.com')
  ->locale('de')
  ->accountMail('mail@example.com')
  ->accountName('admin')
  ->accountPass('pw')
  ->dbPrefix('drupal_')
  ->sqliteDbUrl('sites/default/.ht.sqlite')
  ->disableUpdateStatusModule()
  ->siteInstall('minimal')
  ->run();
```
