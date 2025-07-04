<?php

namespace CI3Htmx;

abstract class Component
{
    protected ?string $stateKey = null;
    protected ?string $wrapperId = null;
    protected string $componentName = '';

    public function __construct()
    {
        $this->componentName = (new \ReflectionClass($this))->getShortName();
    }

    public static function load(array $props = []): string
    {
        $instance = new static();

        foreach ($props as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }

        $instance->stateKey = $instance->generateStateId();
        $instance->wrapperId = 'wrap_' . substr($instance->stateKey, 4);
        $instance->persistState();

        return $instance->render();
    }

    public function setStateKey(string $key): void
    {
        $this->stateKey = $key;
        $this->wrapperId = 'wrap_' . substr($key, 4);
    }

    public function getStateKey(): ?string
    {
        return $this->stateKey;
    }

    public function loadStateFromSession(): void
    {
        if (!$this->stateKey || !isset($_SESSION['htmx_components'][$this->stateKey])) {
            return;
        }

        $data = $_SESSION['htmx_components'][$this->stateKey];

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
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

    protected function generateStateId(): string
    {
        return 'cmp_' . str_replace('.', '', uniqid('', true));
    }

    public function render(): string
    {
        $html = $this->renderContent();

        $csrfName = defined('CI_VERSION') ? $this->csrfName() : 'csrf_token';
        $csrfHash = defined('CI_VERSION') ? $this->csrfHash() : '';

        ob_start();
?>
        <div id="<?= esc($this->wrapperId) ?>" class="htmx-component-wrapper" hx-target="this" hx-swap="replace">
            <div class="htmx-indicator">Memuat...</div>

            <input type="hidden" name="state_id" value="<?= esc($this->stateKey) ?>">
            <input type="hidden" name="component" value="<?= esc($this->componentName) ?>">
            <input type="hidden" name="<?= esc($csrfName) ?>" value="<?= esc($csrfHash) ?>">

            <?= $html ?>
        </div>
<?php
        return ob_get_clean();
    }

    protected function csrfName(): string
    {
        return config_item('csrf_token_name') ?? 'csrf_token';
    }

    protected function csrfHash()
    {
        return $this->getCI()->security->get_csrf_hash() ?? null;
    }

    public function renderComponentOnly(): string
    {
        return $this->renderContent();
    }

    abstract public function renderContent(): string;

    protected function renderComponentView(): string
    {
        $CI = $this->getCI();

        $viewPath = APPPATH . 'views/components/' . $this->componentName . '.view.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("Component view file not found: {$viewPath}");
        }

        $class = new \ReflectionClass($this);
        $path  = dirname($class->getFileName());

        // Ekstrak semua properti publik
        foreach ((new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();
            $$name = $this->$name;
        }

        // Sistem vars
        $state_id  = $this->stateKey;
        $component = $this->componentName;

        ob_start();
        include $viewPath;
        return ob_get_clean();
    }

    protected function getCI()
    {
        return get_instance(); // Untuk akses $this->input, $this->security, dll
    }
}
