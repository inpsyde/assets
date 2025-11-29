<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

/**
 * Interface for assets that support priority-based ordering.
 * Lower priority values are processed first.
 */
interface PrioritizedAsset
{
    /**
     * Get the priority for asset registration order.
     *
     * @return int
     */
    public function priority(): int;

    /**
     * Set the priority for asset registration order. Lower = earlier.
     *
     * @param int $priority
     *
     * @return static
     */
    public function withPriority(int $priority): self;
}
