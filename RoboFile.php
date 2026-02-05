<?php

use Robo\Tasks;

class RoboFile extends Tasks
{
    use \Boedah\Robo\Task\Drush\Tasks;

    public function test(): void
    {
        $this->stopOnFail(true);
        $this->taskPHPUnit(__DIR__ . '/phpunit-11.5.51.phar')
            ->option('testdox')
            ->option('disallow-test-output')
            ->option('strict-coverage')
            ->option('-d error_reporting=-1')
            ->bootstrap('vendor/autoload.php')
            ->arg('tests')
            ->run();
    }
}
