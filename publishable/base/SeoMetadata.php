<?php

namespace App\Models;

use App\Models\Concerns\SerializesLocalizedStrings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

class SeoMetadata extends Model
{
    use HasTranslations;
    use SerializesLocalizedStrings;

    protected $table = 'seo_metadata';

    protected $guarded = [];

    protected $touches = ['seoable'];

    public array $translatable = [
        'title',
        'description',
        'keywords',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
