<?php

namespace Pg\Libraries\Acl;

use Pg\Libraries\Acl\Resource;
use BeatSwitch\Lock\Resources\Resource as IResource;
use BeatSwitch\Lock\Resources\SimpleResource;

class Action implements IResource
{

    /**
     * Action gid
     * 
     * @var string 
     */
    protected $gid;

    /**
     * Resource
     * 
     * @var Pg\Libraries\Acl\Resource 
     */
    private $resource;

    /**
     * Constructor
     * 
     * @param Pg\Libraries\Acl\Resource $resource Resource gid
     * @throws \BadMethodCallException
     */
    public function __construct(Resource $resource = null)
    {
        if (is_null($resource)) {
            $resource = new SimpleResource('', 0);
        } else {
            $this->resource = $resource;
        }
    }

    /**
     * The main identifier for the resource
     *
     * @return int|null
     */
    public function getGid()
    {
        return $this->gid;
    }

    /**
     * Get current resource
     * @return Pg\Libraries\Acl\Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * The string value for the type of resource
     *
     * @return string
     */
    public function getResourceType()
    {
        return $this->getResource()->getResourceType();
    }

    /**
     * The main identifier for the resource
     *
     * @return int|null
     */
    public function getResourceId()
    {
        return $this->getResource()->getResourceId();
    }

    /**
     * Allowed callback
     * 
     */
    public function onAllowed()
    {
        
    }

    /**
     * Denied callback
     * 
     */
    public function onDenied()
    {
        
    }

}
