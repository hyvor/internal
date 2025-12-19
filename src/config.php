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

    /**
     * Auth settings
     */
    'auth' => [

        /**
         * Whether to add auth routes
         * /api/auth/check - get the current user
         * /api/auth/login - login redirect
         * /api/auth/signup - signup redirect
         */
        'routes' => true,

        /**
         * If routes is true, set the domain restriction
         * By default, added to all domains
         */
        'routes_domain' => '{any}',

    ],

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
