<?php

namespace CI3Htmx\Controller;

use CI3Htmx\Component;

class HtmxComponent extends \CI_Controller
{
    public function _remap(string $component, array $params = []): void
    {
        $className = ucfirst($component);
        $fqcn = "App\\Components\\{$className}";

        if (!class_exists($fqcn)) {
            show_404();
            return;
        }

        $this->load->library('session');

        $instance = new $fqcn();
        $instance->setProps(array_merge($this->input->post() ?? [], ['params' => $params]));

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $instance->handleRequest(); // Optional future feature
            } else {
                $instance->mount($params);
            }
        } catch (\Throwable $e) {
            $instance->error = $e->getMessage();
        }

        echo $instance->render();
    }
}
