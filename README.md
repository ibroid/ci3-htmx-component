# CI3 HTMX Component

Mini framework Livewire-like untuk CodeIgniter 3 menggunakan HTMX with Painless javascript. Support stateful components, nested component, action dll.

## 🚀 Fitur

- Component-based rendering
- Stateful viewmodel
- Full HTMX kompitable
- Nested component support
- CSRF + session-aware

---

## 📦 Instalasi

### 1. Tambahkan ke composer.json (jika lokal)

```bash
composer require ibroid/ci3-htmx-component:dev-master
```

### 2. Aktifkan autoload di CodeIgniter 3

Edit `application/config/config.php`:

```php
$config['composer_autoload'] = FCPATH . 'vendor/autoload.php';
```

### 3. Salin file view wrapper

Copy `views/components/_wrapper.php` dari package ini ke:

```
application/views/components/_wrapper.php
```

### 4. Tambahkan routing controller

Buat class controller dibawah ini:

```php
// controllers/Htmx_action.php
use use CI3Htmx\Controller\HtmxAction;
class Htmx_action extends CI3Htmx\Controller\HtmxAction {}

// controllers/Htmx_component.php
use CI3Htmx\Controller\HtmxComponent;
class Htmx_component extends HtmxComponent {};
```

---

## 📄 Contoh Penggunaan

### 1. Buat komponen: `Counter.php`

```php

namespace App\Components;

use CI3Htmx\Component;

class Counter extends Component
{
  public $count = 0;

  public function increment()
  {
    $this->count++;
  }

  public function render(): string
  {
    $this->initIds();

    return $this->renderWrapper(
      $this->renderContent()
    );
  }

  public function renderContent(): string
  {
    return $this->view('components/counter', [
      'count' => $this->count
    ]);
  }
}

```

### 2. View komponen: `application/views/components/counter.php`

```php
<div>
  <h3>Jumlah: <?= $count ?></h3>

  <button
    hx-include="closest .htmx-component-wrapper"
    hx-post="/htmx_action/Counter/increment"
    hx-target="#<?= $state_id ?>"
    hx-swap="outerHTML">
    Tambah
  </button>
</div>
```

### 3. Load komponen
```php
<div>
    <?= App\Components\Counter::load; ?>
</div>
```

---

## ✅ Struktur Komponen

- `mount($params)` → menerima parameter awal
- `render()` → mengembalikan HTML
- `action()` → method yang dipanggil via HTMX

---

## 📂 Struktur Direktori

```
ci3-htmx-component/
├── composer.json
├── src/
│   ├── Component.php
│   └── Controller/
│       └── HtmxAction.php
│       └── HtmxComponent.php
├── views/
│   └── components/
│       └── _wrapper.php
```

---

## 🤝 Kontribusi

Silakan fork, PR, atau gunakan langsung sebagai package lokal. Cukup ganti `ibroid` jadi namespace sendiri.

---

## ⚠️ Catatan

- Package ini dirancang untuk project legacy **CodeIgniter 3**
- Semua komponen harus berada di namespace `App\Components`
- Mengandalkan session bawaan CI3 untuk persist state

---

MIT License.
