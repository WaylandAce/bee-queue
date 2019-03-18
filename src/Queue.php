<?php
declare(strict_types=1);

namespace Dynatech\Libraries\BeeQueue;


class Queue
{
    /** @var string */
    protected $name;
    /** @var array */
    protected $settings = [];
    /** @var \Redis */
    protected $redis;

    /**
     * Queue constructor.
     * @param string $name
     * @param array $settings
     */
    public function __construct(
        string $name,
        array $settings = []
    ) {
        $this->name = $name;

        $redis = $settings['redis'];

        if (isset($redis['instance'])) {
            $this->redis = $redis['instance'];
        } else {
            $redis['host'] = $redis['host'] ?? '127.0.0.1';
            $redis['port'] = $redis['port'] ?? 6379;
            $redis['password'] = $redis['password'] ?? false;

            $this->redis = new \Redis();
            $this->redis->connect($redis['host'], $redis['port']);
            if ($redis['password']) {
                $this->redis->auth($redis['password']);
            }
        }

        $this->settings = [
            'redis'     => $redis,
            'storeJobs' => $settings['storeJobs'] ?? false,
            'keyPrefix' => ($settings['prefix'] ?? 'bq') . ':' . $this->name . ':'
        ];
    }

    /**
     * @param array $data
     * @return Job
     * @throws \Exception
     */
    public function createJob(array $data): Job
    {
        return new Job($this, null, $data);
    }

    /**
     * @param string $str
     * @return string
     */
    public function toKey(string $str): string
    {
        return $this->settings['keyPrefix'] . $str;
    }

    /**
     * @param mixed ...$args
     * @return mixed
     * @throws \Exception
     */
    public function evalScript(...$args)
    {
        $name       = array_shift($args);
        $argsCount  = array_shift($args);

        $path = __DIR__ . '/lua/' . $name . '.lua';
        if (! file_exists($path)) {
            throw new \Exception('Lua script not found');
        }

        // TODO: Cache lua script
        $script = file_get_contents($path);
        $sha    = $this->redis->script('load', $script);

        $response = $this->redis->evalSha($sha, $args, $argsCount);
        if ($response === false) {
            throw new \Exception($this->redis->getLastError());
        }

        return $response;
    }

    /**
     * @return bool
     */
    public function isStoreJobs(): bool
    {
        return $this->settings['storeJobs'] === true;
    }
}
