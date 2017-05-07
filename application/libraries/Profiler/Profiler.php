<?php

namespace Pg\Libraries\Profiler;

class Profiler
{
    private $renderer;
    private $ci;
    private $profiling = array();

    public function __construct($ci)
    {
        $this->ci = $ci;
    }

    /**
     * Set renderer
     *
     * @param \Pg\Libraries\Profiler\Renderer\IProfilerRenderer $renderer
     *
     * @return \Pg\Libraries\Profiler\Profiler
     */
    public function setRenderer(Renderer\IProfilerRenderer $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Get renderer
     *
     * @return Renderer\IProfilerRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Renders profiling data
     *
     * @return string
     */
    public function render()
    {
        return $this->getRenderer()->render($this->ci->view, $this->getProfilingData());
    }

    /**
     * Get profiling data
     *
     * @return array
     */
    public function getProfilingData()
    {
        if (empty($this->profiling)) {
            $this->profiling = $this->compile();
        }

        return $this->profiling;
    }

    /**
     * Compile profiling data
     *
     * @return array
     */
    private function compile()
    {
        // TODO: think about standartization
        return array(
            'vars'       => var_export($this->ci->view->getVars(true), 1),
            'acl'        => $this->getAcl(),
            'benchmarks' => $this->getBenchmarks(),
            'memory'     => $this->getMemoryUsage(),
            'db'         => $this->getDb(),
            'request'    => array(
                'method'     => filter_input(INPUT_SERVER, 'REQUEST_METHOD'),
                'controller' => $this->ci->router->fetch_class(),
                'action'     => $this->ci->router->fetch_method(),
                'uri'        => $this->ci->uri->uri_string,
                'params'     => array(
                    'get'  => filter_input_array(INPUT_GET),
                    'post' => filter_input_array(INPUT_POST),
                ),
            ),
        );
    }

    private function getAcl()
    {
        
    }
    
    private function getBenchmarks()
    {
        $profile = array();
        foreach (array_keys($this->ci->benchmark->marker) as $marker_key) {
            // We match the "end" marker so that the list ends
            // up in the order that it was defined
            if (preg_match("/(.+?)_end/i", $marker_key, $match) &&
                    isset($this->ci->benchmark->marker[$match[1] . '_end']) &&
                    isset($this->ci->benchmark->marker[$match[1] . '_start'])) {
                $profile[$match[1]] = $this->ci->benchmark->elapsed_time($match[1] . '_start', $marker_key);
            }
        }

        return $profile;
    }

    private function getDb()
    {
        // TODO: Count repeated queries
        $db_object = null;

        // Determine which databases are currently connected to
        foreach (get_object_vars($this->ci) as $CI_object) {
            if (is_object($CI_object) && is_subclass_of(get_class($CI_object), 'CI_DB') or 'ci_db' === strtolower(get_parent_class($CI_object))) {
                $db_object = $CI_object;
            }
        }
        if (is_null($db_object)) {
            return array(
                'hostname'         => '',
                'database'         => '',
                'queries'          => array(),
                'queries_time'     => 0,
                'memcached_tables' => array(),
            );
        }
        $queries = array();
        $total_time = 0;
        foreach ($db_object->queries as $i => $query) {
            $queries[$i] = array(
                'query' => $query,
                'time'  => $db_object->query_times[$i],
            );
            $total_time += $db_object->query_times[$i];
        }

        return array(
            'hostname'         => $db_object->hostname,
            'database'         => $db_object->database,
            'queries'          => $queries,
            'queries_time'     => $total_time,
            'memcached_tables' => $db_object->memcached_tables,
        );
    }

    private function getMemoryUsage()
    {
        return memory_get_usage();
    }
}
