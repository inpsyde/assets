<?php

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

return [
    [
        'handle' => 'foo',
        'url' => 'foo.css',
        'location' => Asset::FRONTEND,
        'type' => Style::class,
    ],
    [
        'handle' => 'bar',
        'url' => 'bar.js',
        'location' => Asset::FRONTEND,
        'type' => Script::class,
    ],
];
