<?php

namespace CheckerplateSoftware\LaravelMutex\Traits;

use Carbon\Carbon;
use CheckerplateSoftware\LaravelMutex\Mutex;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait RequiresCommandGuard
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Get us up and running
        $this->initializeCommandGuard();

        // Respect others and they will respect you
        parent::initialize($input, $output);
    }

    /**
     * Sets up the mutex locker for us with this command.
     */
    protected function initializeCommandGuard()
    {
        // Setup the lock
        $lock = new Mutex(sha1(__CLASS__));

        // Attempt to acquire the lock
        if (! $lock->acquireLock(Carbon::now()->addSeconds($this->lockValidityInSeconds ?? 60)->timestamp, $this->lockTimeoutInSeconds ?? 0)) {
            exit(1);
        }

        // Ensure we always release the lock (if we got one)
        register_shutdown_function(function () use ($lock) {
            $lock->releaseLock();
        });
    }

}