import { Head, usePage } from '@inertiajs/react';
import type { SeoMetadata } from '@/types/content';

interface SeoProps {
    seo?: SeoMetadata;
    title?: string;
    description?: string;
    keywords?: string[];
    image?: string;
}

export default function SeoMeta({ seo, title, description, keywords, image }: SeoProps) {
    const { appName, appUrl, currentUrl, alternateUrls = {}, locale } = usePage<any>().props;

    const metaTitle = seo?.title || title || appName;
    const metaDescription = seo?.description || description || '';
    const metaKeywords = (seo?.keywords || keywords || []).join(', ');
    const metaImage = seo?.og_image || image || `${appUrl}/images/og-default.jpg`;

    return (
        <Head>
            <title>{metaTitle}</title>
            <meta name="description" content={metaDescription} />
            {metaKeywords && <meta name="keywords" content={metaKeywords} />}

            {/* OpenGraph */}
            <meta property="og:title" content={metaTitle} />
            <meta property="og:description" content={metaDescription} />
            <meta property="og:image" content={metaImage} />
            <meta property="og:url" content={currentUrl} />
            <meta property="og:type" content="website" />

            {/* Twitter */}
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" content={metaTitle} />
            <meta name="twitter:description" content={metaDescription} />
            <meta name="twitter:image" content={metaImage} />

            {/* Canonical & Alternates */}
            <link rel="canonical" href={currentUrl} />
            {Object.entries(alternateUrls).map(([lang, url]) => (
                <link key={lang} rel="alternate" hrefLang={lang} href={url as string} />
            ))}
        </Head>
    );
}
