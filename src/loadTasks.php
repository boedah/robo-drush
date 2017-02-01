<?php

namespace Boedah\Robo\Task\Drush;

trait loadTasks
{
    /**
     * @param string $pathToDrush
     * @return DrushStack
     */
    protected function taskDrushStack($pathToDrush = 'drush')
    {
        return $this->task(DrushStack::class, $pathToDrush);
    }
}
