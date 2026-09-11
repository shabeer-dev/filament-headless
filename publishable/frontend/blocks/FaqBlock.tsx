import React, { useState } from 'react';

export default function FaqBlock({ data }: { data: any }) {
    const [openIdx, setOpenIdx] = useState<number | null>(0);

    if (!data || !data.items || data.items.length === 0) return null;

    return (
        <section className="py-20 bg-background border-b border-outline/10">
            <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                {(data.title || data.description) && (
                    <div className="text-center mb-16">
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

                <div className="space-y-4">
                    {data.items.map((item: any, idx: number) => {
                        const isOpen = openIdx === idx;
                        return (
                            <div
                                key={idx}
                                className="rounded-xl bg-surface border border-outline/15 overflow-hidden transition-all duration-200"
                            >
                                <button
                                    type="button"
                                    onClick={() => setOpenIdx(isOpen ? null : idx)}
                                    className="w-full px-6 py-5 flex items-center justify-between text-left focus:outline-none cursor-pointer"
                                >
                                    <span className="font-semibold text-text-white text-base sm:text-lg pr-4">
                                        {item.question}
                                    </span>
                                    <span className={`text-primary transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`}>
                                        ▼
                                    </span>
                                </button>
                                {isOpen && (
                                    <div className="px-6 pb-6 pt-2 text-on-surface-variant text-sm sm:text-base leading-relaxed border-t border-outline/10">
                                        {item.answer}
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
