<?php

namespace CI3Htmx;

abstract class Component
{
    public $error = null;
    protected $props = [];
    protected $stateKey;
    protected ?string $wrapperId = null;

    public function setProps(array $props = []): void
    {
        $this->props = $props;
        $this->stateKey = $props['state_id'] ?? null;

        if ($this->stateKey && isset($_SESSION['htmx_components'][$this->stateKey])) {
            $this->hydrateFromSession($_SESSION['htmx_components'][$this->stateKey]);
        }
    }

    public function persistState(): void
    {
        if (!$this->stateKey) {
            throw new \Exception("State key is not set.");
        }

        $data = [];

        foreach ((new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();
            $data[$name] = $this->$name;
        }

        $_SESSION['htmx_components'][$this->stateKey] = $data;
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
        return 'cmp_' . str_replace('.', '', uniqid('', true));
    }

    public function renderWrapper(string $html): string
    {
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
            'wrapper_id'    => $this->wrapperId,
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

    protected function initState(): void
    {
        if (!$this->stateKey) {
            $this->stateKey = $this->generateStateId();
        }
    }

    abstract public function render(): string;

    protected function view(string $path, array $data = []): string
    {
        $CI = &get_instance();
        $data['state_id'] = $this->stateKey; // inject otomatis
        return $CI->load->view($path, $data, true);
    }

    public function renderContent(): string
    {
        $CI = &get_instance();
        return $this->view('components/' . strtolower($this->componentName), []);
    }

    protected function initIds(): void
    {
        if (!$this->stateKey) {
            $this->stateKey = 'cmp_' . bin2hex(random_bytes(8));
        }

        if (!$this->wrapperId) {
            $this->wrapperId = 'wrap_' . substr($this->stateKey, 4); // cocokkan dgn cmp_xxx
        }
    }

    public function renderComponentOnly(): string
    {
        $this->initIds(); // pastikan state_id dan wrapper_id tersedia

        return '<div id="' . $this->stateKey . '">' . $this->renderContent() . '</div>';
    }

    public function setStateKey(string $key): void
    {
        $this->stateKey = $key;
        $this->wrapperId = 'wrap_' . substr($key, 4); // cocokkan format wrapper ID
    }

    public function loadStateFromSession(): void
    {
        if (!$this->stateKey) {
            throw new \Exception("State key is not set.");
        }

        if (!isset($_SESSION['htmx_components'][$this->stateKey])) {
            return;
        }

        $data = $_SESSION['htmx_components'][$this->stateKey];

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
