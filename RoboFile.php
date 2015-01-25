<?php

require_once __DIR__ . '/vendor/autoload.php';

class RoboFile extends \Robo\Tasks
{
    use \Boedah\Robo\Task\Drush\loadTasks;

    public function test()
    {
        $this->stopOnFail(true);
        $this->taskPHPUnit()
            ->arg('--strict')
            ->arg('tests')
            ->run();
    }
}
