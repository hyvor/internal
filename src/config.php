<?php

return [

    /**
     * Which component is this?
     * See `src/InternalApi/ComponentType.php` for available components
     *
     * core - hyvor.com
     * talk - talk.hyvor.com
     * ..
     */
    'component' => 'core',

    /**
     * @see InternalConfig::$commsKey
     */
    'comms_key' => env('COMMS_KEY', ''),

    /**
     * @see InternalConfig::$deployment
     */
    'deployment' => env('DEPLOYMENT', 'on-prem'),

    /**
     * This is the domain that the app is running on.
     * Routes are only accessible from this domain.
     * @todo: refactor this into `route.` setting
     */
    'domain' => env('APP_DOMAIN', '{any}'),

    /**
     * Instance URL
     * Where is the core component running?
     */
    'instance' => env('HYVOR_INSTANCE', 'https://hyvor.com'),

    /**
     * Private instance URL
     * To communicate in a private network
     */
    'private_instance' => env('HYVOR_PRIVATE_INSTANCE'),

    /**
     * Whether to fake auth and billing
     * Only possible in the local environment
     */
    'fake' => env('HYVOR_FAKE', false),

    'i18n' => [

        /**
         * Folder that contains the locale JSON files
         */
        'folder' => base_path('locales'),

        /**
         * Default locale
         */
        'default' => 'en-US',

    ],

];
