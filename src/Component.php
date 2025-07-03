<?php

namespace CI3Htmx;

abstract class Component
{
    public $error = null;
    protected $props = [];
    protected $stateKey;

    public function setProps(array $props = []): void
    {
        $this->props = $props;
        $this->stateKey = $props['state_id'] ?? null;

        if ($this->stateKey && isset($_SESSION['htmx_components'][$this->stateKey])) {
            $this->hydrateFromSession($_SESSION['htmx_components'][$this->stateKey]);
        }
    }

    protected function persistState(): void
    {
        $_SESSION['htmx_components'][$this->stateKey] = $this->dehydrateToSession();
    }

    protected function hydrateFromSession(array $state): void
    {
        foreach ($state as $prop => $value) {
            if (property_exists($this, $prop)) {
                $this->$prop = $value;
            }
        }
    }

    protected function dehydrateToSession(): array
    {
        $ref = new \ReflectionClass($this);
        $state = [];
        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $state[$prop->getName()] = $this->{$prop->getName()};
        }
        return $state;
    }

    protected function generateStateId(): string
    {
        return uniqid('cmp_', true);
    }

    public function renderWrapper(string $html): string
    {
        if (!$this->stateKey) {
            $this->stateKey = $this->generateStateId();
        }

        $this->persistState();
        $CI = &get_instance();
        $csrfName = $CI->security->get_csrf_token_name();
        $csrfHash = $CI->security->get_csrf_hash();
        $component = (new \ReflectionClass($this))->getShortName();

        $loadingHtml = method_exists($this, 'renderLoading')
            ? $this->renderLoading()
            : '<div class="htmx-indicator">Memuat...</div>';

        $errorHtml = ($this->error && method_exists($this, 'renderError'))
            ? $this->renderError($this->error)
            : ($this->error ? htmlspecialchars($this->error) : '');

        return $CI->load->view('components/_wrapper', [
            'state_id'      => $this->stateKey,
            'component'     => $component,
            'csrf_name'     => $csrfName,
            'csrf_hash'     => $csrfHash,
            'slot'          => $html,
            'error_html'    => $errorHtml,
            'loading_html'  => $loadingHtml,
        ], true);
    }

    public static function load(array $params = []): string
    {
        $CI = &get_instance();
        $CI->load->library('session');

        $instance = new static();
        $instance->mount($params);

        return $instance->render();
    }

    public function mount(array $params = []): void {}

    abstract public function render(): string;
}
