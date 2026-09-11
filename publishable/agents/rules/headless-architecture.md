# Headless CMS + Inertia React Architectural Rules

You must strictly adhere to these fundamental architectural principles whenever modifying, creating, or refactoring code in this project:

## 1. The Singleton Rule (Filament Pages)
- **No Table / List Views for Pages:** Website pages (Home, About, Services, etc.) represent singleton entities (exactly 1 row in the database table).
- **Base Class:** All page resources MUST extend `App\Filament\Resources\SingletonResource` and override `canCreate()` / `canDelete()` to return `false`.
- **Edit Page:** Page resources MUST use `App\Filament\Pages\ManageSingletonPage` for their edit route, which automatically mounts record ID `1`. Never create `ListRecords` pages for content models.

## 2. Universal Translatability Rule
- **Database Storage:** All translatable text columns MUST be defined as `$table->json('column_name')->nullable()` in migrations.
- **Model Definition:** Models MUST use `Spatie\Translatable\HasTranslations` and declare all localized fields inside the `public array $translatable = [...]` property.
- **Form Wrapper:** In Filament resources, the entire form schema MUST be wrapped in:
  ```php
  Translations::make('translations')->columnSpanFull()->schema([
      // Tabs, Sections, Repeaters
  ])
  ```
- **Serialization:** Models MUST use `App\Models\Concerns\SerializesLocalizedStrings`. This ensures that when the controller outputs JSON to Inertia, translatable fields are flattened into clean strings for the active locale, automatically falling back to English when empty. The frontend should NEVER have to unpack `{ en: '...', ar: '...' }` objects manually.

## 3. Media Management Rule
- **Spatie Media Collections:** Never store raw file paths or upload filenames as string columns in content tables.
- **Model Trait:** Content models MUST implement `Spatie\MediaLibrary\HasMedia` and use `App\Models\Concerns\InteractsWithOptimizedMedia`.
- **Register Collections:** Define media slots inside `registerMediaCollections()` using `$this->addMediaCollection('name')->singleFile()`.
- **Filament Upload:** Use `Forms\Components\SpatieMediaLibraryFileUpload` bound directly to the collection name, with `->dehydrated(false)`.

## 4. Reusable Form Schema Pattern
- Avoid duplicating form schemas. Use `App\Filament\Helpers\FormFields` static methods (`heroSectionTab()`, `overviewTab()`, `footerCtaTab()`, `seoSection()`, `icon()`, `colSpanSelect()`).

## 5. Frontend & Inertia Rules
- **Controller Pipeline:** Page controllers MUST extend `App\Http\Controllers\ContentPageController` and implement `model(): string` and `component(): string`. The base controller automatically handles `rememberForever` locale-keyed caching.
- **Type-Safe Routing:** NEVER use raw `<a>` tags with hardcoded paths. Always use `<Link>` with Laravel Wayfinder route functions (`import { routeName } from '@/routes'`).
- **Layout Wrap:** Every Inertia page component MUST be wrapped using `PageName.layout = (page: React.ReactNode) => <AppLayout>{page}</AppLayout>;`.
- **RTL & Direction:** Multi-language directional switching is handled globally by `AppLayout` using `dir={locale === 'ar' ? 'rtl' : 'ltr'}`. Never hardcode LTR styles that break Arabic layout.
