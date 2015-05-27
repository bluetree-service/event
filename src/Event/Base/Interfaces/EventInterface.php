<?php

namespace ClassEvent\Event\Base\Interfaces;

interface EventInterface
{
    public function isPropagationStopped();
    public function stopPropagation();
}
