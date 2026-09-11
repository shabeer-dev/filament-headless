import React from 'react';

export default function StatsBlock({ data }: { data: any }) {
    if (!data || !data.items || data.items.length === 0) return null;

    return (
        <section className="py-16 bg-surface-container-low border-b border-outline/10">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {data.headline && (
                    <h2 className="text-center text-sm font-semibold uppercase tracking-wider text-primary mb-10">
                        {data.headline}
                    </h2>
                )}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    {data.items.map((stat: any, idx: number) => (
                        <div key={idx} className="p-6 rounded-xl bg-surface border border-outline/10">
                            <div className="text-3xl sm:text-5xl font-extrabold text-text-white mb-2">
                                {stat.value}
                                {stat.suffix && <span className="text-primary">{stat.suffix}</span>}
                            </div>
                            <div className="text-sm font-medium text-on-surface-variant">
                                {stat.label}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
