import React from 'react';
import HeroBlock from './HeroBlock';
import StatsBlock from './StatsBlock';
import BentoGridBlock from './BentoGridBlock';
import OverviewBlock from './OverviewBlock';
import FaqBlock from './FaqBlock';
import CtaBlock from './CtaBlock';

export interface ContentBlock {
    type: string;
    data: Record<string, any>;
}

interface BlockRendererProps {
    blocks?: ContentBlock[];
    customBlocks?: Record<string, React.FC<{ data: any }>>;
}

const DEFAULT_BLOCK_REGISTRY: Record<string, React.FC<{ data: any }>> = {
    hero: HeroBlock,
    stats: StatsBlock,
    bento_grid: BentoGridBlock,
    overview: OverviewBlock,
    faq: FaqBlock,
    cta: CtaBlock,
};

export default function BlockRenderer({ blocks, customBlocks = {} }: BlockRendererProps) {
    if (!blocks || !Array.isArray(blocks) || blocks.length === 0) {
        return null;
    }

    const registry = { ...DEFAULT_BLOCK_REGISTRY, ...customBlocks };

    return (
        <div className="flex flex-col w-full">
            {blocks.map((block, index) => {
                const Component = registry[block.type];

                if (!Component) {
                    console.warn(`[BlockRenderer] Unknown block type: "${block.type}"`);
                    return null;
                }

                return <Component key={`${block.type}-${index}`} data={block.data} />;
            })}
        </div>
    );
}
