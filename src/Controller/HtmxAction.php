<?php
namespace CI3Htmx\Controller;

use CI3Htmx\Component;

class HtmxAction extends \CI_Controller
{
    public function _remap(string $component, array $params = []): void
    {
        $method = $params[0] ?? null;
        $className = ucfirst($component);
        $fqcn = "App\\Components\\{$className}";

        if (!class_exists($fqcn) || !$method) {
            show_404();
            return;
        }

        $this->load->library('session');

        $instance = new $fqcn();
        $instance->setProps($this->input->post() ?? []);

        try {
            if (method_exists($instance, $method)) {
                call_user_func_array([$instance, $method], array_slice($params, 1));
            } else {
                throw new \Exception("Method {$method} not found in {$fqcn}");
            }
        } catch (\Throwable $e) {
            $instance->error = $e->getMessage();
        }

        echo $instance->render();
    }
}
