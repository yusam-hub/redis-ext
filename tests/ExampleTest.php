<?php

namespace YusamHub\RedisExt\Tests;

use YusamHub\RedisExt\RedisExt;

class ExampleTest extends \PHPUnit\Framework\TestCase
{
    /*public function testDefault()
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
    }*/

    public function testDefault2()
    {
        $redisExt = new RedisExt([
            'host' => 'redis-host',
            'prefix' => 'tt_'
        ]);

        $this->assertFalse($redisExt->has('testKey'));

        $this->assertTrue($redisExt->put('testKey', 100, 1));
        $this->assertTrue($redisExt->get('testKey') === 100);
        sleep(2);
        $this->assertFalse($redisExt->has('testKey'));

        $this->assertTrue($redisExt->put('testKey', 100));
        $this->assertTrue($redisExt->has('testKey'));
        $this->assertTrue($redisExt->del('testKey'));
        $this->assertFalse($redisExt->has('testKey'));

    }
}