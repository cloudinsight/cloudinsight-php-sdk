<?php

namespace CloudInsight\Tests;

require_once 'StatsdSub.php';
use PHPUnit_Framework_TestCase;

class StatsdTest extends PHPUnit_Framework_TestCase
{
    private $statsd;

    public function setUp()
    {
        parent::setUp();
        $this->statsd       = new StatsdSub;
        $this->statsd->rate = 0.5;
    }

    public function testGauge()
    {
        $this->statsd->gauge('yaiba.test_gauge', 8080, ['yaiba:gauge', 'yaiba:cloud_insight'], 0.5);
        $this->assertEquals(1, count($this->statsd->buffer()));
        $this->assertEquals('yaiba.test_gauge:8080|g|@0.5|#yaiba:gauge,yaiba:cloud_insight', $this->statsd->buffer()[0]);
    }

    public function testGaugeWithDefaultOption()
    {
        $this->statsd->gauge('yaiba.test_gauge');
        $this->assertEquals(1, count($this->statsd->buffer()));
        $this->assertEquals('yaiba.test_gauge:1|g|@1', $this->statsd->buffer()[0]);
    }

    public function testGagueWithConstantTags()
    {
        $tags = array('yaiba:cloud_insight');
        $sd   = new StatsdSub('localhost', 8251, array('max_buffer_size' => 10, 'constant_tags' => array('yabia:yabia')));
        $sd->gauge('yaiba.test_gauge', 9090, $tags);
        $this->assertEquals(1, count($sd->buffer()));
        $this->assertEquals('yaiba.test_gauge:9090|g|@1|#yaiba:cloud_insight,yabia:yabia', $sd->buffer()[0]);
        $sd->gauge('yaiba.test_gauge', 9091, $tags);
        $this->assertEquals('yaiba.test_gauge:9091|g|@1|#yaiba:cloud_insight,yabia:yabia', $sd->buffer()[1]);
    }

    public function testIncrement()
    {
        $this->statsd->increment('yaiba.test_increment', 8080, ['yaiba:increment', 'yaiba:cloud_insight'], 0.5);
        $this->assertEquals(1, count($this->statsd->buffer()));
        $this->assertEquals('yaiba.test_increment:8080|c|@0.5|#yaiba:increment,yaiba:cloud_insight',
            $this->statsd->buffer()[0]);
    }

    public function testIncrementWithDefaultOption()
    {
        $this->statsd->increment('yaiba.test_increment');
        $this->assertEquals(1, count($this->statsd->buffer()));
        $this->assertEquals('yaiba.test_increment:1|c|@1', $this->statsd->buffer()[0]);
    }

    public function testDecrement()
    {
        $this->statsd->decrement('yaiba.test_decrement', 8080, ['yaiba:decrement', 'yaiba:cloud_insight'], 0.5);
        $this->assertEquals(1, count($this->statsd->buffer()));
        $this->assertEquals('yaiba.test_decrement:-8080|c|@0.5|#yaiba:decrement,yaiba:cloud_insight', $this->statsd->buffer()[0]);
    }

    public function testBufferWithMax()
    {
        $this->incrementByTimes(30);
        $this->assertEquals(30, count($this->statsd->buffer()));
        $this->incrementByTimes(20);
        $this->statsd->increment('test_buffer2');
        $this->assertEquals(1, count($this->statsd->buffer()));
        $this->assertEquals('test_buffer2:1|c|@1', $this->statsd->buffer()[0]);
    }

    private function incrementByTimes($num)
    {
        for ($i = 0; $i < $num; $i++) {
            $this->statsd->increment('test_buffer');
        }
    }
}
