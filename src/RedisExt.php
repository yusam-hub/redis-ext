<?php

namespace YusamHub\RedisExt;

class RedisExt
{
    /**
     * @var \Redis
     */
    protected \Redis $redis;

    /**
     * @param array $config
     * @throws \RedisException
     */
    public function __construct(array $config = [])
    {
        $this->redis = new \Redis();
        $this->redis->connect($config['host']??'localhost', $config['port']??6379);
        $this->redis->select($config['dbIndex']??0);
        $this->redis->setOption(\Redis::OPT_PREFIX, $config['prefix']??'');
        $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_JSON);
        $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
    }

    /**
     * @return \Redis
     */
    public function redis(): \Redis
    {
        return $this->redis;
    }

    /**
     * @param string $queue
     * @param object $value
     * @return false|int|\Redis
     * @throws \RedisException
     */
    public function queuePush(string $queue, object $value)
    {
        return $this->redis->rPush(sprintf("q_%s",$queue), serialize($value));
    }

    /**
     * @param string $queue
     * @return object|null
     * @throws \RedisException
     */
    public function queueShift(string $queue): ?object
    {
        $serializeData = $this->redis->lPop(sprintf("q_%s",$queue));

        if ($serializeData === false) {
            return null;
        }

        return unserialize($serializeData);
    }

    /**
     * @param string $queue
     * @return int
     * @throws \RedisException
     */
    public function queueCount(string $queue): int
    {
        return intval($this->redis->lLen(sprintf("q_%s",$queue)));
    }
}