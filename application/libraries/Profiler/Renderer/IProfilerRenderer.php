<?php

namespace Pg\Libraries\Profiler\Renderer;

interface IProfilerRenderer
{
    public function render(\Pg\Libraries\View $view, array $profiling);
}
