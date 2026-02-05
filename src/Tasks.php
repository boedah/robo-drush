<?php

namespace Boedah\Robo\Task\Drush;

use Robo\Collection\CollectionBuilder;

trait Tasks
{
    protected function taskDrushStack(string $pathToDrush = 'drush'): CollectionBuilder|DrushStack
    {
        return $this->task(DrushStack::class, $pathToDrush);
    }
}
