<?php

namespace YusamHub\RedisExt;

class RedisExt
{
    public bool $isDebugging = false;
    /**
     * @var \Redis
     */
    protected \Redis $redis;

    protected ?\Closure $onDebugLogCallback = null;

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
     * @param \Closure|null $callback
     * @return void
     */
    public function onDebugLogCallback(?\Closure $callback): void
    {
        $this->onDebugLogCallback = $callback;
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function debugLog(string $message, array $context = []): void
    {
        if (!$this->isDebugging) return;

        if (!is_null($this->onDebugLogCallback)) {
            $callback = $this->onDebugLogCallback;
            $callback($message, $context);
            return;
        }

        echo $message;
        if (!empty($context)) {
            echo json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        echo PHP_EOL;
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

            $val = unserialize($result);

            $this->debugLog("GET", [
                'val' => is_object($val) ? (array) $val : $val,
                'is_scalar' => is_scalar($val),
                'is_array' => is_array($val),
                'is_object' => is_object($val),
            ]);

            return $val;
        }
        return $default;
    }

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return bool
     */
    public function put(string $key, $value, int $ttl = 0): bool
    {
        try {
            if ($ttl > 0) {
                $result = $this->redis->setex($key, $ttl, serialize($value));
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
     * @param string $key
     * @param int $ttl
     * @param \Closure $value
     * @param bool $useCache //for debug use or not use cache
     * @param bool $freshKey //for debug (always clean key)
     * @return mixed
     */
    public function returnScalarOrArray(string $key, int $ttl, \Closure $value, bool $useCache = true, bool $freshKey = false)
    {
        if ($freshKey) {
            $this->del($key);
        }

        if ($useCache) {
            if (!$this->has($key)) {
                $val = $value();
                if (!(is_scalar($val) || is_array($val))) {
                    throw new \RuntimeException("Invalid value");
                }
                if (!$this->put($key, $val, $ttl)) {
                    throw new \RuntimeException("Unable to put value");
                }
                return $val;
            }

            $val = $this->get($key);
            if (is_object($val)) {
                $val = (array) $val;
            }
            if (is_scalar($val) || is_array($val)) {
                return $val;
            }
        }

        $val = $value();
        if (is_object($val)) {
            $val = (array) $val;
        }
        if (!(is_scalar($val) || is_array($val))) {
            throw new \RuntimeException("Invalid value");
        }
        return $val;
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