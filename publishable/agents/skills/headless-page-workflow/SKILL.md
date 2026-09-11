---
name: headless-page-workflow
description: Standard operating procedure for creating, modifying, and extending singleton content pages in the Filament Headless CMS + Inertia React architecture.
---

# Headless Page Workflow Skill

This skill guides you through creating, updating, or refactoring pages within the project's Headless CMS + Inertia React architecture.

## Creating a New Page

### Fast Track (Artisan CLI)
Run the generator command:
```bash
php artisan headless:make-page {Name}
```
This automatically produces the 7-layer vertical slice:
1. Migration (`database/migrations/xxxx_create_page_{name}_table.php`)
2. Model (`App\Models\Content\Page{Name}`)
3. Filament Singleton Resource & Manage Page (`App\Filament\Resources\Content\Page{Name}\...`)
4. Content Controller (`App\Http\Controllers\Page\{Name}Controller`)
5. Inertia React Page & Partials (`resources/js/pages/{Name}.tsx`, `partials/*`)
6. TypeScript Interface (`resources/js/types/content.ts`)
7. Route Definition in `routes/web.php`

---

## Manual Verification Checklist for Any Page

When inspecting or manually building a page, verify all 7 layers:

1. **Migration Check:**
   - Table name: `page_{name}`
   - Columns for text/lists MUST be `$table->json(...)`
   - Single row architecture (standard timestamps + JSON content)

2. **Model Check:**
   - Extends `Model` and implements `HasMedia`
   - Traits: `HasSEO`, `HasTranslations`, `InteractsWithOptimizedMedia`, `SerializesLocalizedStrings`
   - Attribute: `#[ObservedBy(ContentObserver::class)]`
   - `$translatable` lists all JSON text attributes
   - `$casts` arrays for repeatable items (repeaters)
   - `registerMediaCollections()` defines singleFile collections

3. **Filament Resource Check:**
   - Extends `SingletonResource` (never standard `Resource`)
   - Route points to `ManageSingletonPage`
   - Schema is completely wrapped in `Translations::make('translations')`
   - Common tabs leverage `App\Filament\Helpers\FormFields`

4. **Controller Check:**
   - Extends `ContentPageController`
   - Returns `model()` class name and `component()` view name

5. **Route Check:**
   - Registered under the `{locale}` route prefix group in `routes/web.php`

6. **Frontend React Check:**
   - Consumes `{ content: Page{Name} }`
   - Root page assigns `.layout = (page) => <AppLayout>{page}</AppLayout>`
   - All internal links use Laravel Wayfinder
   - Includes `<SeoMeta />`

7. **Busting & Testing Cache:**
   - Saving in Filament triggers `ContentObserver::clearCache()` across `en`, `ar`, `es`.
   - Run `php artisan optimize:clear` and test page rendering.
