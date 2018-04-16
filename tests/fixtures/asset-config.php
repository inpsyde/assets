<?php

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

return [
    [
        'handle' => 'foo',
        'url' => 'foo.css',
        'type' => Asset::FRONTEND,
        'class' => Style::class
    ],
    [
        'handle' => 'bar',
        'url' => 'bar.js',
        'type' => Asset::FRONTEND,
        'class' => Script::class
    ],
];
