<?php

namespace Inpsyde\Assets\Loader;

interface LoaderInterface
{
    /**
     * @param mixed $resource
     *
     * @return array
     */
    public function load($resource): array;
}
