<?php

namespace YusamHub\RedisExt\Tests;

use YusamHub\RedisExt\RedisExt;
use YusamHub\RedisExt\Tests\Demo\DemoQueueObject;

class ExampleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \RedisException
     */
    public function testDefault()
    {
        $redisExt = new RedisExt([
            'host' => 'redis-host',
            'prefix' => 'tt_'
        ]);

        print_r([
            'prefix' => $redisExt->redis()->_prefix(""),
            'valueWithPrefix' => $redisExt->redis()->_prefix("my_value")
        ]);

        $demoQueueObject = new DemoQueueObject();
        $demoQueueObject->id = 1;
        $demoQueueObject->title = 'title1';

        print_r($demoQueueObject);

        $redisExt->queuePush("default", $demoQueueObject);

        print_r($redisExt->queueCount('default'));

        sleep(1);

        $o = $redisExt->queueShift('default');

        print_r($o);

        print_r($redisExt->queueCount('default'));

        $this->assertTrue(true);
    }
}