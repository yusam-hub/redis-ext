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
     */
    public function __construct(array $config = [])
    {
        try {
            $this->redis = new \Redis();
            $this->redis->connect($config['host']??'localhost', $config['port']??6379);
            $this->redis->select($config['dbIndex']??0);
            $this->redis->setOption(\Redis::OPT_PREFIX, $config['prefix']??'');
            $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_JSON);
            $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        } catch (\RedisException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return \Redis
     */
    public function redis(): \Redis
    {
        return $this->redis;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        try {
            $result = $this->redis->get($key);
        } catch (\RedisException $e) {
            $result = false;
        }
        if (is_string($result)) {
            return unserialize($result);
        }
        return $default;
    }

    /**
     * @param string $key
     * @param $value
     * @param int $expire
     * @return bool
     */
    public function put(string $key, $value, int $expire = 0): bool
    {
        try {
            if ($expire > 0) {
                $result = $this->redis->setex($key, $expire, serialize($value));
            } else {
                $result = $this->redis->set($key, serialize($value));
            }
            if (is_bool($result)) {
                return $result;
            }
            return false;
        } catch (\RedisException $e) {
            return false;
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        try {
            $result = $this->redis->exists($key);
            if (is_int($result)) {
                return $result === 1;
            }
            return false;
        } catch (\RedisException $e) {
            return false;
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function del(string $key): bool
    {
        try {
            $result = $this->redis->del($key);
            if (is_int($result)) {
                return $result === 1;
            }
            return false;
        } catch (\RedisException $e) {
            return false;
        }
    }

    /**
     * @param string $queue
     * @param mixed $value
     * @return null|int
     */
    public function queuePush(string $queue, $value): int
    {
        try {
            $result = $this->redis->rPush(sprintf("q_%s",$queue), serialize($value));
            if (is_int($result)) {
                return $result;
            }
            return 0;
        } catch (\RedisException $e) {
            return 0;
        }
    }

    /**
     * @param string $queue
     * @return mixed|null
     */
    public function queueShift(string $queue)
    {
        try {
            $serializeData = $this->redis->lPop(sprintf("q_%s", $queue));
            if (is_string($serializeData)) {
                return unserialize($serializeData);
            }
            return null;
        } catch (\RedisException $e) {
            return null;
        }
    }

    /**
     * @param string $queue
     * @return int
     */
    public function queueCount(string $queue): int
    {
        try {
            $result =$this->redis->lLen(sprintf("q_%s",$queue));
            if (is_int($result)) {
                return $result;
            }
            return 0;
        } catch (\RedisException $e) {
            return 0;
        }
    }
}