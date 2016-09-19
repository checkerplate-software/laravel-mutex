<?php

namespace CheckerplateSoftware\LaravelMutex\Drivers;

interface MutexDriver
{
    /**
     * Attempt to acquire a lock.
     *
     * @param string $name The name of the lock
     * @param int $expires When the lock should no longer be considered 'held'
     * @param int $timeout How long to wait for a lock before giving up
     * @return bool Whether or not the lock was acquired
     */
    public function acquireLock(string $name, int $expires, int $timeout);

    /**
     * Release a previously-held lock.
     *
     * @param string $name The name of the lock
     * @return bool Whether or not the lock was released
     */
    public function releaseLock(string $name);

    /**
     * Determine if a lock is currently being held.
     *
     * @param string $name The name of the lock
     * @return bool Whether or not a lock is being held
     */
    public function isLocked(string $name);
}