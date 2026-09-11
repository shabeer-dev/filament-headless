// ─── Shared Base Headless Interfaces ──────────────────────────────────────────

export interface MediaItem {
    id: number;
    collection_name: string;
    name: string;
    file_name: string;
    mime_type: string;
    size: number;
    original_url: string;
    preview_url: string;
}

export interface HeroFields {
    hero_label?: string | null;
    hero_title?: string | null;
    hero_highlighted?: string | null;
    hero_description?: string | null;
    hero_cta_primary?: string | null;
    hero_cta_primary_route?: string | null;
    hero_cta_secondary?: string | null;
    hero_cta_secondary_route?: string | null;
}

export interface OverviewFields {
    overview_title: string;
    overview_subtitle?: string | null;
    overview_description: string;
    overview_right_block_type?: 'image' | 'content' | 'none' | null;
    overview_right_block_icon?: string | null;
    overview_right_block_title?: string | null;
    overview_right_block_description?: string | null;
}

export interface SeoMetadata {
    title?: string | null;
    description?: string | null;
    keywords?: string[] | null;
    og_image?: string | null;
}

export interface FooterCtaFields {
    footer_cta_title?: string | null;
    footer_cta_button?: string | null;
    footer_cta_route?: string | null;
    seo?: SeoMetadata;
}
