<?php

class RoboFile extends \Robo\Tasks
{
    use \Boedah\Robo\Task\Drush\loadTasks;

    public function test()
    {
        $this->stopOnFail(true);
        $this->taskPHPUnit()
            ->arg('-v')
            ->arg('-d error_reporting=-1')
            ->option('disallow-test-output')
            ->option('report-useless-tests')
            ->option('strict-coverage')
            ->arg('tests')
            ->run();
    }
}
