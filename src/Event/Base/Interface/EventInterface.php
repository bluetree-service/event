<?php

namespace ClassEvent\Event\Base;

interface EventInterface
{
    public function isPropagationStopped();
}
