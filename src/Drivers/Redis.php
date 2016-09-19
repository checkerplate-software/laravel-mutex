<?php

namespace CheckerplateSoftware\LaravelMutex\Drivers;

use Predis\Client;

class Redis implements MutexDriver
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Redis driver constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Attempt to acquire a lock.
     *
     * @param string $name The name of the lock
     * @param int $expires When the lock should no longer be considered 'held'
     * @param int $timeout How long to wait for a lock before giving up
     * @return bool Whether or not the lock was acquired
     */
    public function acquireLock(string $name, int $expires, int $timeout)
    {
        // Keep track of when we started
        $start = time();

        // Let's party
        do {
            // Attempt to acquire our lock
            if ($this->writeToRedis($name, $expires) || $this->recover($name, $expires)) {
                return true;
            }

            // If the user set a timeout, wait for that to elapse
            if ($timeout > 0) {
                sleep(1);
            }
        } while($timeout > 0 && (time() < $start + $timeout));

        // We failed to get a lock. Sorry mum.
        return false;
    }

    /**
     * Release a previously-held lock.
     *
     * @param string $name The name of the lock
     * @return bool Whether or not the lock was released
     */
    public function releaseLock(string $name)
    {
        return (bool)$this->deleteFromRedis($name);
    }

    /**
     * Determine if a lock is currently being held.
     *
     * @param string $name The name of the lock
     * @return bool Whether or not a lock is being held
     */
    public function isLocked(string $name)
    {
        // Get the value
        $value = $this->getFromRedis($name);

        // For a lock to be held, it must be set and not have expired
        return ! is_null($value) && $value > time();
    }

    /**
     * Get the current value stored in Redis.
     *
     * @param $name
     * @return string|null
     */
    private function getFromRedis($name)
    {
        return $this->client->get($name);
    }

    /**
     * Set the current value in Redis.
     *
     * @param $name
     * @param $expires
     * @return bool
     */
    private function writeToRedis($name, $expires)
    {
        return (bool)$this->client->setnx($name, $expires);
    }

    /**
     * Delete the value stored in Redis.
     *
     * @param $name
     * @return bool
     */
    private function deleteFromRedis($name)
    {
        return (bool)$this->client->del($name);
    }

    /**
     * Recover a lock that has expired.
     *
     * @param $name
     * @param $expires
     * @return int
     */
    private function recover($name, $expires)
    {
        // Get the current value stored in Redis
        $storedExpiry = $this->getFromRedis($name);

        // If we were provided a value, then see if it has expired
        if (! is_null($storedExpiry) && $storedExpiry < time()) {
            $this->deleteFromRedis($name);
        }

        // Attempt to write our new value to redis
        return $this->writeToRedis($name, $expires);
    }
}