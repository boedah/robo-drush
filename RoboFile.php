<?php

class RoboFile extends \Robo\Tasks
{
    use \Boedah\Robo\Task\Drush\loadTasks;

    public function test()
    {
        $this->stopOnFail(true);
        $this->taskPHPUnit()
            ->arg('--strict')
            ->arg('-v')
            ->arg('-d error_reporting=-1')
            ->arg('tests')
            ->run();
    }
}
