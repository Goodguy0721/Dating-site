<?php

namespace Pg\Libraries;

class EventDispatcher extends \Symfony\Component\EventDispatcher\EventDispatcher
{
    /**
     *  instance of the class
     */
    protected static $instance;

    /**
     * Protected constructor to prevent creating a new instance of the
     *
     * Singleton via the `new` operator from outside of this class.
     */
    private function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the instance of the Singleton instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the Singleton instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }

    /**
     * Returns the Singleton instance of this class.
     *
     * @return Singleton instance.
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
