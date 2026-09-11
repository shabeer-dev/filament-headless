import React from 'react';
import { Link } from '@inertiajs/react';

export default function CtaBlock({ data }: { data: any }) {
    if (!data || !data.title) return null;

    return (
        <section className="relative overflow-hidden py-20 bg-surface border-t border-outline/10 text-center">
            {data.background_image && (
                <div
                    className="absolute inset-0 bg-cover bg-center opacity-15 pointer-events-none"
                    style={{ backgroundImage: `url(/storage/${data.background_image})` }}
                />
            )}
            <div className="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 className="text-3xl sm:text-5xl font-extrabold text-text-white mb-8 tracking-tight">
                    {data.title}
                </h2>
                {data.button_text && (
                    <Link
                        href={data.route ? `/${data.route}` : '#'}
                        className="inline-block px-10 py-4 rounded-xl font-bold bg-primary text-on-primary hover:bg-primary/90 transition-all duration-200 text-lg shadow-lg hover:shadow-primary/25"
                    >
                        {data.button_text}
                    </Link>
                )}
            </div>
        </section>
    );
}
