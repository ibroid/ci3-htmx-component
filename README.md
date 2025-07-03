# CI3 HTMX Component

Mini framework Livewire-like untuk CodeIgniter 3 menggunakan HTMX with Painless javascript. Support stateful components, nested component, dan action dll.

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
composer require yourvendor/ci3-htmx-component
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

Tambahkan file ini ke `application/controllers`:

```php
// HtmxComponent.php
require_once APPPATH . 'vendor/yourvendor/ci3-htmx-component/src/Controller/HtmxComponent.php';

// HtmxAction.php
require_once APPPATH . 'vendor/yourvendor/ci3-htmx-component/src/Controller/HtmxAction.php';
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
        return $this->renderWrapper(view('components/counter', [
            'count' => $this->count
        ], true));
    }
}
```

### 2. View komponen: `application/views/components/counter.php`

```php
<div>
    <h3>Jumlah: <?= $count ?></h3>

    <button hx-post="/htmx_action/Counter/increment" hx-target="closest div" hx-swap="outerHTML">
        Tambah
    </button>
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

Silakan fork, PR, atau gunakan langsung sebagai package lokal. Cukup ganti `yourvendor` jadi namespace sendiri.

---

## ⚠️ Catatan

- Package ini dirancang untuk project legacy **CodeIgniter 3**
- Semua komponen harus berada di namespace `App\Components`
- Mengandalkan session bawaan CI3 untuk persist state

---

MIT License.