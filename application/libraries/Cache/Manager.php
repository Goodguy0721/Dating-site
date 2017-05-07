<?php

namespace Pg\Libraries\Cache;

class Manager
{
    const PROVIDER_DEFAULT = 'filesystem';
    
    private $fabric;
    
    private $adapters = [];
    
    private $services = [];
    
    public function __construct()
    {
        $this->factory = new \wv\BabelCache\SimpleFactory();
        
        $this->factory->setCacheDirectory(TEMPPATH . 'cache');
    }
    
    public function getAdapter($service)
    {
        if (!array_key_exists($name, $this->adapters)) {
            $cache = $this->factory->getCache($this->services[$service]['provider']);
            $this->adapters[$name] = new \wv\BabelCache\Adapter\Jailed($cache, $service, true);
        }   
        
        return $this->adapters[$name];
    }
    
    public function registerService($name, $provider='') 
    {
        if (!$provider) {
            $provider = self::PROVIDER_DEFAULT;
        }
        
        $this->services[$name] = ['provider' => $provider];
    }
}
