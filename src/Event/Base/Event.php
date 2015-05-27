<?php
/**
 * @todo tags
 * @todo launch count
 * @todo last trigger
 * @todo tracer
 * @todo stop propagation
 * @todo log event
 */

namespace ClassEvent\Event\Base;

use ClassEvent\Event\Base\Interfaces\EventInterface;

abstract class Event implements EventInterface
{
    public function isPropagationStopped()
    {
        
    }

    public function stopPropagation()
    {

    }
}
