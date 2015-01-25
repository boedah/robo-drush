<?php

require __DIR__ . '/vendor/autoload.php';

class RoboFile extends \Robo\Tasks
{
    use \Boedah\Robo\Task\Drush;

    public function test()
    {
        $this->stopOnFail(true);
        $this->taskPHPUnit()
            ->arg('--strict')
            ->arg('tests')
            ->run();
    }
}
