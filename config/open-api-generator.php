<?php

return [
    /**
     * Your API version code.
     */
    'version' => '1.0.0',

    /**
     * The name of your API.
     */
    'title' => 'Example API',

    /**
     * An array of server configurations. Each one contains a "url" and an optional "description".
     */
    'servers' => [
        [
            'url' => env('APP_URL', 'http://example.com'),
            'description' => 'The main production server',
        ],
    ],
];
