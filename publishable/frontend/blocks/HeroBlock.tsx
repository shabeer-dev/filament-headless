import React from 'react';
import { Link } from '@inertiajs/react';

export default function HeroBlock({ data }: { data: any }) {
    if (!data) return null;

    return (
        <section className="relative overflow-hidden py-24 md:py-32 bg-background border-b border-outline/10">
            {data.background_image && (
                <div
                    className="absolute inset-0 bg-cover bg-center opacity-20 pointer-events-none"
                    style={{ backgroundImage: `url(/storage/${data.background_image})` }}
                />
            )}
            <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                {data.label && (
                    <span className="inline-block px-3 py-1 mb-6 text-xs font-semibold uppercase tracking-wider text-primary bg-primary/10 rounded-full border border-primary/20">
                        {data.label}
                    </span>
                )}
                <h1 className="text-4xl sm:text-6xl font-extrabold tracking-tight text-text-white mb-6">
                    {data.title}{' '}
                    {data.highlighted && <span className="text-primary">{data.highlighted}</span>}
                </h1>
                {data.description && (
                    <p className="max-w-3xl mx-auto text-lg md:text-xl text-on-surface-variant mb-10 leading-relaxed">
                        {data.description}
                    </p>
                )}
                <div className="flex flex-wrap justify-center gap-4">
                    {data.cta_primary && (
                        <Link
                            href={data.cta_primary_route ? `/${data.cta_primary_route}` : '#'}
                            className="px-8 py-3.5 rounded-lg font-semibold bg-primary text-on-primary hover:bg-primary/90 transition-all duration-200"
                        >
                            {data.cta_primary}
                        </Link>
                    )}
                    {data.cta_secondary && (
                        <Link
                            href={data.cta_secondary_route ? `/${data.cta_secondary_route}` : '#'}
                            className="px-8 py-3.5 rounded-lg font-semibold bg-surface border border-outline/20 text-text-white hover:bg-surface-container transition-all duration-200"
                        >
                            {data.cta_secondary}
                        </Link>
                    )}
                </div>
            </div>
        </section>
    );
}
