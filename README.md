# Robo Drush Extension

Extension to execute Drush commands in [Robo](https://github.com/Codegyre/Robo).

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/1d842f01-b2c4-415a-b372-12af7a5516e0/mini.png)](https://insight.sensiolabs.com/projects/1d842f01-b2c4-415a-b372-12af7a5516e0) [![Build Status](https://travis-ci.org/boedah/robo-drush.svg?branch=master)](https://travis-ci.org/boedah/robo-drush)  [![Latest Stable Version](https://poser.pugx.org/boedah/robo-drush/v/stable)](https://packagist.org/packages/boedah/robo-drush) [![Total Downloads](https://poser.pugx.org/boedah/robo-drush/downloads)](https://packagist.org/packages/boedah/robo-drush) [![Latest Unstable Version](https://poser.pugx.org/boedah/robo-drush/v/unstable)](https://packagist.org/packages/boedah/robo-drush) [![License](https://poser.pugx.org/boedah/robo-drush/license)](https://packagist.org/packages/boedah/robo-drush) 

Runs Drush commands in stack. You can define global options for all commands (like Drupal root and uri).

The option -y assumed by default but can be overridden on calls to `exec()` by passing `false` as the second parameter.

## Table of contents

- [Versions](#versions)
- [Installation](#installation)
- [Testing](#testing)
- [Usage](#usage)
- [Examples](#examples)

## Installation

For new projects (and Robo >= 1.0.0-RC1), just do:

    composer require --dev boedah/robo-drush

For older versions of Robo, use:

- `~1.0`: Robo <= 0.4.5
- `~2.1`: Robo >= 0.5.2

## Testing

`composer test`

## Usage

Use the trait (according to your used version) in your RoboFile:

```php
class RoboFile extends \Robo\Tasks
{
    // if you use robo-drush ~2.1 for Robo >=0.5.2, or robo-drush >3 for Robo >=1.0.0-RC1
    use \Boedah\Robo\Task\Drush\loadTasks;

    // if you use ~1.0 for Robo ~0.4
    use \Boedah\Robo\Task\Drush;
    
    //...
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
