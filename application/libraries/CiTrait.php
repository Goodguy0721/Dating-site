<?php

namespace Pg\Libraries;

/**
 * Class TestCase
 */
trait CiTrait
{
    protected $ci;

    /*public function invokeMethod(&$object, $method_name, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method_name);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }*/

    public function ci()
    {
        if (empty($this->ci)) {
            $this->ci = get_instance();
        }

        return $this->ci;
    }
}
