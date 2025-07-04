<?php

namespace CI3Htmx\Controller;

use CI3Htmx\Component;

class HtmxAction extends \CI_Controller
{
    public function _remap(string $componentName, array $params = []): void
    {
        $componentClass = "App\\Components\\" . ucfirst($componentName);

        if (!class_exists($componentClass)) {
            show_404();
            return;
        }

        $stateId = $this->input->post('state_id');
        $method  = $params[0] ?? null;

        if (!$stateId || !$method) {
            show_error("State ID or method not provided", 400);
            return;
        }

        $instance = new $componentClass();
        $instance->setStateKey($stateId);
        $instance->loadStateFromSession();

        if (!method_exists($instance, $method)) {
            show_error("Method {$method} not found", 404);
            return;
        }

        call_user_func([$instance, $method]);

        $instance->persistState();

        echo $instance->renderComponentOnly();
    }
}
