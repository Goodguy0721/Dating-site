<?php

namespace Pg\Libraries\Acl;

use BeatSwitch\Lock\Resources\Resource as IResource;

/**
 * A contract to identify a resource which can be used to set permissions on
 */
class Resource implements IResource
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int|null
     */
    protected $id = 0;

    /**
     * The string value for the type of resource
     *
     * @return string
     */
    public function getResourceType()
    {
        return $this->type;
    }

    /**
     * The main identifier for the resource
     *
     * @return int|null
     */
    public function getResourceId()
    {
        return $this->id;
    }

}
