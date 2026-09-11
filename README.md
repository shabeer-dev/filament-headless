# 🚀 Filament Headless (`shabeer-dev/filament-headless`)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/shabeer-dev/filament-headless.svg?style=flat-square)](https://packagist.org/packages/shabeer-dev/filament-headless)
[![Total Downloads](https://img.shields.io/packagist/dt/shabeer-dev/filament-headless.svg?style=flat-square)](https://packagist.org/packages/shabeer-dev/filament-headless)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/shabeer-dev/filament-headless/lint.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/shabeer-dev/filament-headless/actions)
[![License](https://img.shields.io/packagist/l/shabeer-dev/filament-headless.svg?style=flat-square)](LICENSE)

A modular, turnkey toolkit and CLI generator for building high-performance, multilingual websites using **Filament v5 as a Headless CMS** and **Inertia.js v3 + React 19** as the decoupled frontend, fully aligned with **Laravel Boost MCP & Guidelines**.

---

## ⚡ Key Highlights

- **Singleton Page Pattern**: Eliminates confusing tables for website pages (Home, About, Services, etc.). Clicking a page navigates directly to its singular edit form.
- **Pure Modular Block Builder**: Construct dynamic pages where admins can add, reorder, and remove blocks freely using Filament's `Builder` component. Rendered on the frontend via a dynamic React `<BlockRenderer />`.
- **Laravel Boost MCP & Skill Integration**:
  - Automatically synchronizes with `boost.json` (`filament/filament`, `spatie/laravel-medialibrary`, `spatie/laravel-translatable`).
  - Registers the `headless-architecture` skill in `boost.json` and `.agents/skills/`.
  - Harmonizes guidelines in `GEMINI.md` and `AGENTS.md` so Boost-aware LLMs respect the architecture.
- **Pest 4 Feature Test Generation**: `php artisan headless:make-page` automatically scaffolds feature tests (`tests/Feature/Pages/{Name}PageTest.php`) asserting localized 200 HTTP responses, Inertia component rendering, and cache invalidation in accordance with Boost testing rules.
- **Interactive Prompts Installer**: Powered by `Laravel\Prompts` for an interactive CLI setup wizard (language multi-selection, default locale, starter pages).
- **System Diagnostics Doctor**: `php artisan headless:doctor` validates PHP extensions, public storage symlinks, Spatie media tables, Wayfinder routes, Boost sync, and Pest configuration.
- **Configurable Multi-Language Engine**: Centralized in `config/headless-kit.php`. Add or remove languages without touching PHP or React code.
- **Dynamic RTL / LTR Support**: Automatically detects right-to-left languages (Arabic, Hebrew, Farsi, Urdu) and adjusts layout direction.
- **Real-Time SERP & Social Previews**: Live Google search snippet and Twitter/Facebook OpenGraph card simulation directly inside the Filament admin form with live character count analysis.
- **Architecture Linter**: `php artisan headless:lint` audits content models, Filament resources, and React pages to enforce architectural purity and catch drift.
- **AI Agent Context Generator**: `php artisan headless:agent-context` compiles a real-time Markdown ground-truth digest (`.agents/headless-context.md`) for LLM agents.
- **One-Command Scaffolding**: `php artisan headless:make-page {Name}` scaffolds all 7 layers of a page in seconds.

---

## 📦 Installation in Any Laravel Project

Install the package via Composer:

```bash
composer require shabeer-dev/filament-headless
```

Then run the interactive installer:

```bash
php artisan headless:install
```

The installer will prompt you to:
1. Select supported languages (English, Arabic, Spanish, French, German, Chinese, etc.).
2. Pick your default application language.
3. Select starter pages to scaffold immediately (`Home`, `About`, `Services`, `Contact`).
4. Automatically synchronize with `boost.json` and inject guidelines into `GEMINI.md` / `AGENTS.md`.

---

## 🛠️ CLI Commands & Generators

| Command | Purpose |
| :--- | :--- |
| `php artisan headless:install` | Run interactive wizard to publish config, base classes, layouts, rules, and sync Boost |
| `php artisan headless:doctor` | Run diagnostics (PHP, symlinks, media table, Wayfinder, Boost sync, Pest, Pint) |
| `php artisan headless:make-page {Name}` | Scaffold a complete 7-layer singleton page with section presets & Pest test |
| `php artisan headless:make-section {Page} {Name}` | Scaffold a modular section partial component and Filament schema snippet |
| `php artisan headless:make-block {Name}` | Scaffold a new Filament Builder block and matching React block component |
| `php artisan headless:lint` | Audit codebase to detect and prevent architectural drift |
| `php artisan headless:agent-context` | Generate real-time Markdown schema map for AI coding agents |

---

## 🧱 Pure Modular Block Builder

### Backend (Filament)
Allow editors to freely compose and re-order content using `BlockCatalog::all()`:

```php
use App\Filament\Blocks\BlockCatalog;
use Filament\Forms\Components\Builder;

Builder::make('blocks')
    ->label('Page Sections')
    ->blocks(BlockCatalog::all())
    ->collapsible()
    ->cloneable()
```

Built-in blocks include:
- `hero`: Title, highlighted text, badge, dual CTAs, background media.
- `stats`: Counter metrics band with target numbers and suffixes.
- `bento_grid`: Responsive multi-span feature cards with icons and badges.
- `overview`: Split 2-column text and media layout.
- `faq`: Collapsible questions and answers.
- `cta`: Full-width banner with target action.

### Frontend (React)
Render any list of dynamic blocks with one line:

```tsx
import BlockRenderer from '@/components/blocks/BlockRenderer';

export default function ServicesPage({ content }) {
    return <BlockRenderer blocks={content.blocks} />;
}
```

---

## 🧪 Pest Feature Testing (Boost Standard)

Every page generated with `php artisan headless:make-page` comes with a ready-to-run Pest 4 feature test:

```php
// tests/Feature/Pages/ServicesPageTest.php
use Inertia\Testing\AssertableInertia as Assert;

it('renders the services page successfully for default locale', function () {
    $response = $this->get('/en/services');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Services')
        ->has('content')
    );
});
```

Run tests with:
```bash
php artisan test --compact
```

---

## 📄 License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
