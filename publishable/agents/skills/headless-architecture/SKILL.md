---
name: headless-architecture
description: "Guidelines and best practices for developing and maintaining the Filament Headless CMS + Inertia React architecture. Use when creating content models, Filament resources, translatable fields, Inertia pages, or Pest tests."
license: MIT
metadata:
  framework: filament
  frontend: inertia-react
---

# Filament Headless CMS & Inertia Architecture

This project uses **Filament v5 as a Headless CMS** with an **Inertia.js v3 + React 19** frontend.

## Foundational Principles

1. **The Singleton Rule for Content Pages:**
   - Content pages (Home, About, Services, Contact, etc.) contain exactly one record in the database.
   - All page resources MUST extend `App\Filament\Resources\SingletonResource` and override `canCreate()` and `canDelete()` to return `false`.
   - Never create Filament `ListRecords` table pages for content pages. The resource route must navigate directly to an instance of `App\Filament\Pages\ManageSingletonPage` which resolves record `1`.

2. **Universal Translatability:**
   - Translatable content must be defined as `$table->json(...)` columns in migrations.
   - Models must implement `Spatie\Translatable\HasTranslations` and list all localized fields in `public array $translatable = [...]`.
   - Wrap the entire Filament resource form in:
     ```php
     Translations::make('translations')->columnSpanFull()->schema([ ... ])
     ```
   - All content models MUST use `App\Models\Concerns\SerializesLocalizedStrings`. This intercepts `toArray()` and auto-flattens JSON dictionaries into clean strings for the current application locale, with English fallback.

3. **Media Management:**
   - Models must implement `Spatie\MediaLibrary\HasMedia` and use `App\Models\Concerns\InteractsWithOptimizedMedia`.
   - Register single-file collections via `$this->addMediaCollection('name')->singleFile()`.
   - Bind file uploads in Filament with `SpatieMediaLibraryFileUpload::make('name')->collection('name')->dehydrated(false)`. Never save raw paths in database string columns.

4. **Caching & Invalidation:**
   - Controllers must extend `App\Http\Controllers\ContentPageController`.
   - Page content queries are cached forever via `Cache::rememberForever()` with a key format of `{model_name}_content_{locale}`.
   - Models MUST be observed by `#[ObservedBy(ContentObserver::class)]` which clears the cache across all supported locales upon save.

5. **Type-Safe Full-Stack Routing:**
   - Always use Laravel Wayfinder route functions (`import { services } from '@/routes'`) for all frontend navigation links.
   - Every React page must set `.layout = (page) => <AppLayout>{page}</AppLayout>`.
   - Multi-language directional switching (RTL/LTR) is handled by `AppLayout` based on `LocaleManager::isRtl()`.

6. **Pest Testing Standards:**
   - Write feature tests asserting localized 200 HTTP responses, Inertia component names, and `content` prop hydration using `AssertableInertia`.
