import React from 'react';

export default function OverviewBlock({ data }: { data: any }) {
    if (!data) return null;

    const isMediaLeft = data.layout === 'media_left';

    return (
        <section className="py-20 bg-surface-container-low border-b border-outline/10">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className={`grid grid-cols-1 lg:grid-cols-2 gap-12 items-center ${isMediaLeft ? 'lg:flex-row-reverse' : ''}`}>
                    <div className={isMediaLeft ? 'lg:order-2' : 'lg:order-1'}>
                        {data.subtitle && (
                            <span className="text-xs font-semibold uppercase tracking-wider text-primary mb-2 block">
                                {data.subtitle}
                            </span>
                        )}
                        <h2 className="text-3xl sm:text-4xl font-bold text-text-white mb-6 leading-tight">
                            {data.title}
                        </h2>
                        {data.description && (
                            <p className="text-on-surface-variant text-base sm:text-lg leading-relaxed mb-6">
                                {data.description}
                            </p>
                        )}
                    </div>
                    {data.image && (
                        <div className={`rounded-2xl overflow-hidden border border-outline/15 ${isMediaLeft ? 'lg:order-1' : 'lg:order-2'}`}>
                            <img
                                src={`/storage/${data.image}`}
                                alt={data.title}
                                className="w-full h-auto object-cover max-h-[480px]"
                                loading="lazy"
                            />
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}
