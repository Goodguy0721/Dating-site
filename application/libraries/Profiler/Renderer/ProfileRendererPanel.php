<?php

namespace Pg\Libraries\Profiler\Renderer;

class ProfileRendererPanel implements IProfilerRenderer
{
    /**
     * Renders profiling data
     *
     * @param \Pg\Libraries\View $view
     * @param array              $profiling
     *
     * @return string
     */
    public function render(\Pg\Libraries\View $view, array $profiling)
    {
        $this->decorate($profiling);
        $view->assign('profiling', $profiling);

        return $view->fetch('profiler_panel');
    }

    /**
     * Correct profiling data format
     *
     * @param array $profiling
     */
    private function decorate(&$profiling)
    {
        $unit = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
        $profiling['memory'] = round($profiling['memory'] / pow(1024, ($i = floor(log($profiling['memory'], 1024)))), 2) . '&nbsp;' . $unit[$i];

        foreach ($profiling['db']['queries'] as &$query) {
            $query['time'] = round($query['time'], 4);
            $query['query'] = htmlspecialchars($query['query'], null, 'utf-8');
        }
        $profiling['db']['queries_time'] = round($profiling['db']['queries_time'], 4);
        $profiling['db']['queries_num'] = count($profiling['db']['queries']);
        // Sort by time in reverse
        uasort($profiling['db']['queries'], function ($a, $b) {
            if ($a['time'] === $b['time']) {
                return 0;
            } else {
                return ($a['time'] < $b['time']) ? 1 : -1;
            }
        });
    }
}
