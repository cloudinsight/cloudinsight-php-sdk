<?php

namespace CloudInsight;

/**
 * Class Statsd
 */
class Statsd
{

    private $socket;
    private $buffer;
    const ENCODING = 'UTF-8';

    public function __construct($host = 'localhost', $port = 8251, $option = array())
    {
        $this->host            = $host;
        $this->port            = $port;
        $this->buffer          = array();
        $this->max_buffer_size = is_null($option['max_buffer_size']) ? 50 : $option['max_buffer_size'];
        $this->constant_tags   = is_null($option['constant_tags']) ? array() : $option['constant_tags'];
        $this->use_ms          = is_null($option['use_ms']) ? false : $option['use_ms'];
        register_shutdown_function(array(&$this, 'onExit'));
    }

    public function gauge($metric, $value = 1, $tags = array(), $sample_rate = 1.0)
    {
        return $this->report($metric, 'g', $value, $tags, $sample_rate);
    }

    public function increment($metric, $value = 1, $tags = array(), $sample_rate = 1.0)
    {
        return $this->report($metric, 'c', $value, $tags, $sample_rate);
    }

    public function decrement($metric, $value = 1, $tags = array(), $sample_rate = 1.0)
    {
        return $this->report($metric, 'c', -$value, $tags, $sample_rate);
    }

    public function buffer()
    {
        return $this->buffer;
    }

    public function onExit()
    {
        $this->flushBuffer();
    }

    protected function randRate()
    {
        rand(1, 100) / 100;
    }

    private function report($metric, $mtype, $value, $tags, $sample_rate)
    {

        if ((int) ($sample_rate) != 1 && ($this->randRate() > $sample_rate)) {
            return;
        }
        $data = $this->convert($metric, $mtype, $value, $tags, $sample_rate);
        if (!is_null($data)) {
            $this->storeToBuffer($data);
        }
    }

    private function convert($metric, $mtype, $value, $tags, $sample_rate)
    {
        $data = '';
        $data .= $metric . ':' . $value . '|';
        $data .= $mtype . '|';
        $data .= '@' . $sample_rate . '|';
        $tags = array_merge($tags, $this->constant_tags);

        if (!empty($tags)) {
            $data .= '#';
            foreach ($tags as $tag) {
                $data .= $tag . ',';
            }
            $data = chop($data, ',');
        } else {
            $data = chop($data, '|');
        }
        return $data;
    }

    private function storeToBuffer($data)
    {
        $this->buffer[] = $data;
        if (count($this->buffer) >= $this->max_buffer_size) {
            $this->flushBuffer();
        }
    }

    private function flushBuffer()
    {
        $old_buffer   = $this->buffer;
        $this->buffer = array();
        $this->sendToAgent($old_buffer);
    }

    private function sendToAgent($buffer)
    {
        if (count($buffer) <= 0) {return;}
        try {
            if (!($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
                $errorcode = socket_last_error();
                $errormsg  = socket_strerror($errorcode);
                echo ("Couldn't create socket: [$errorcode] $errormsg \n");
            }
            $data = $this->covertToStr($buffer);
            $len  = strlen($data);
            socket_sendto($socket, $data, $len, 0, $this->host, $this->port);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        } finally {
            socket_close($socket);
            // echo "socket has closed successful.\n";
        }
    }

    private function covertToStr($buffer)
    {
        $data = '';
        foreach ($buffer as $element) {
            var_dump(self::ENCODING);
            $encode_elelment = iconv(mb_detect_encoding($element, mb_detect_order(), true), self::ENCODING, $element);
            $data .= "$encode_elelment\n";
        }
        return $data;
    }

}
