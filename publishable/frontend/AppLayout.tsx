import { usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import SeoMeta from '@/components/SeoMeta';
import { setUrlDefaults } from '@/wayfinder';

export default function AppLayout({
    children,
    hideNav = false,
}: PropsWithChildren<{ hideNav?: boolean }>) {
    const { locale, isRtl, locales = {} } = usePage<any>().props;

    // Dynamically update Wayfinder URL generator defaults synchronously during render
    if (locale) {
        setUrlDefaults({ locale });
    }

    // Determine direction dynamically from isRtl prop or locale metadata
    const direction = isRtl ?? (locales[locale]?.dir === 'rtl' || ['ar', 'fa', 'he', 'ur'].includes(locale)) ? 'rtl' : 'ltr';

    return (
        <div dir={direction}>
            <SeoMeta />
            <div
                className={`min-h-screen overflow-x-hidden bg-background font-sans text-on-surface-variant antialiased selection:bg-primary selection:text-on-primary ${direction === 'rtl' ? 'text-right' : 'text-left'}`}
            >
                <main>{children}</main>
            </div>
        </div>
    );
}
