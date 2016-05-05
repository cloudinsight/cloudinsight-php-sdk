<?php

namespace CloudInsight\Tests;

use CloudInsight\Statsd;

/**
 *
 */
class StatsdSub extends Statsd
{

    public $rate;

    protected function randRate()
    {
        return $this->rate;
    }

}
