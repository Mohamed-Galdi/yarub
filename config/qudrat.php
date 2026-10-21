<?php

return [
    // Explicit opt-in, in addition to the environment and local database guard.
    'allow_development_import' => (bool) env('QUDRAT_ALLOW_DEVELOPMENT_IMPORT', false),
    // Explicit owner-approved existing local database; never accepted in testing or production.
    'existing_local_database' => env('QUDRAT_EXISTING_LOCAL_DATABASE'),
];
