<?php declare(strict_types=1); # -*- coding: utf-8 -*-

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
