<?php

namespace CheckerplateSoftware\LaravelMutex;

use Exception;
use Illuminate\Support\Facades\Redis;
use CheckerplateSoftware\LaravelMutex\Drivers;

class Mutex
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Drivers\MutexDriver
     */
    private $driver;

    /**
     * Mutex constructor.
     *
     * @param string $name
     * @param string|null $driver
     */
    public function __construct($name, $driver = null)
    {
        $this->name = 'LaravelMutex:' . $name;
        $this->driver = $this->getDriver($driver);
    }

    /**
     * Get the lock implementation based on the configuration file.
     *
     * @param $driver
     * @return Drivers\MutexDriver
     * @throws Exception
     */
    private function getDriver($driver)
    {
        // Fall back to the default driver as required
        if (is_null($driver)) {
            $driver = config('mutex.default');
        }

        // Get the driver configuration
        $config = config('mutex.drivers.'.$driver, null);
        if (is_null($config)) {
            throw new Exception($driver.' has not been configured.');
        }

        // Get the underlying driver implementation
        switch ($driver) {
            case 'redis':
            default:
                return new Drivers\Redis(Redis::connection($config['connection']));
        }
    }

    /**
     * Attempt to acquire a lock.
     *
     * @param int $expires When the lock should no longer be considered 'held'
     * @param int $timeout How long to wait for a lock before giving up
     * @return bool Whether or not the lock was acquired
     */
    public function acquireLock(int $expires = 0, int $timeout = 0)
    {
        return $this->driver->acquireLock($this->name, $expires, $timeout);
    }

    /**
     * Release a previously-held lock.
     *
     * @return bool Whether or not the lock was released
     */
    public function releaseLock()
    {
        return $this->driver->releaseLock($this->name);
    }

    /**
     * Determine if a lock is currently being held.
     *
     * @return bool Whether or not a lock is being held
     */
    public function isLocked()
    {
        return $this->driver->isLocked($this->name);
    }
}