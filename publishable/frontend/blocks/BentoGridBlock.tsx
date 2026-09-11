import React from 'react';

export default function BentoGridBlock({ data }: { data: any }) {
    if (!data || !data.cards || data.cards.length === 0) return null;

    return (
        <section className="py-20 bg-background border-b border-outline/10">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {(data.title || data.description) && (
                    <div className="text-center max-w-3xl mx-auto mb-16">
                        {data.title && (
                            <h2 className="text-3xl sm:text-4xl font-bold text-text-white mb-4">
                                {data.title}
                            </h2>
                        )}
                        {data.description && (
                            <p className="text-on-surface-variant text-base sm:text-lg">
                                {data.description}
                            </p>
                        )}
                    </div>
                )}

                <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
                    {data.cards.map((card: any, idx: number) => {
                        const colSpan = card.col_span ? `md:col-span-${card.col_span}` : 'md:col-span-4';

                        return (
                            <div
                                key={idx}
                                className={`col-span-12 ${colSpan} p-8 rounded-2xl bg-surface border border-outline/15 hover:border-primary/40 transition-all duration-300 flex flex-col justify-between`}
                            >
                                <div>
                                    {card.badge && (
                                        <span className="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/20 text-primary mb-4">
                                            {card.badge}
                                        </span>
                                    )}
                                    <h3 className="text-xl font-bold text-text-white mb-3">
                                        {card.title}
                                    </h3>
                                    <p className="text-on-surface-variant text-sm leading-relaxed">
                                        {card.description}
                                    </p>
                                </div>
                                {card.image && (
                                    <div className="mt-6 rounded-lg overflow-hidden">
                                        <img
                                            src={`/storage/${card.image}`}
                                            alt={card.title}
                                            className="w-full h-40 object-cover"
                                            loading="lazy"
                                        />
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
