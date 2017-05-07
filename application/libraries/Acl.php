<?php

namespace Pg\Libraries;

use Pg\Libraries\Acl\Caller;
use BeatSwitch\Lock\Manager;
use Pg\Libraries\Acl\Action;
use BeatSwitch\Lock\Drivers\Driver as IDriver;

class Acl
{

    /**
     * Data driver
     * 
     * @var BeatSwitch\Lock\Drivers\Driver
     */
    private $driver;

    /**
     * Caller provider
     * 
     * @var callable|array 
     */
    private $callerProvider;

    /**
     * Current caller lock
     * 
     * @var \BeatSwitch\Lock\Callers\CallerLock
     */
    private $caller;

    /**
     * Acl manager
     * 
     * @var \BeatSwitch\Lock\Manager
     */
    private $manager;

    /**
     * Constructor
     * 
     * @param \BeatSwitch\Lock\Drivers\Driver $driver
     * @param callable|array $callerProvider
     */
    public function __construct(IDriver $driver, $callerProvider)
    {
        $this->driver = $driver;
        $this->callerProvider = $callerProvider;
    }

    /**
     * Get acl manager
     * 
     * @return \BeatSwitch\Lock\Manager
     */
    public function getManager()
    {
        if (!$this->manager) {
            $this->manager = new Manager($this->driver);
        }

        return $this->manager;
    }

    public function checkSimple($action, $resource = null, $resource_id = 0)
    {
        return $this->getCurrentUser()->can($action, $resource, $resource_id);
    }

    private function handle(Action $action, $is_allowed)
    {
        if ($is_allowed) {
            $action->onAllowed();
            return true;
        } else {
            $action->onDenied();
            return false;
        }
    }

    /**
     * Check user acces to the resource
     * 
     * @param Pg\Libraries\Acl\Action $action
     * @param boolean $auto_handle
     * @return boolean
     */
    public function check(Action $action, $auto_handle = true)
    {
        $can = $this->getCurrentUser()->can(
            $action->getGid(), $action->getResourceType(), $action->getResourceId()
        );
        if ($auto_handle) {
            $this->handle($action, $can);
        }
        return $can;
    }

    private function filterCaller(array $caller_raw)
    {
        return filter_var_array($caller_raw, [
            'type' => FILTER_DEFAULT,
            'id' => FILTER_VALIDATE_INT,
            'roles' => [
                'flags' => FILTER_REQUIRE_ARRAY,
            ],
        ]);
    }

    /**
     * Create a caller
     * 
     * @param callable|array $provider
     * @return \BeatSwitch\Lock\Callers\CallerLock
     * @throws \BadMethodCallException
     */
    public function caller($provider = null)
    {
        if (is_callable($provider)) {
            $caller_raw = call_user_func($provider);
        } else {
            $caller_raw = $provider;
        }
        $caller = $this->filterCaller($caller_raw);
        return $this->getManager()->caller(
                new Caller($caller['type'], $caller['id'], $caller['roles'])
        );
    }

    /**
     * Get current caller lock
     * 
     * @return \BeatSwitch\Lock\Callers\CallerLock
     */
    public function getCurrentUser()
    {
        if (!$this->caller) {
            $this->caller = $this->caller($this->callerProvider);
        }
        return $this->caller;
    }

}
