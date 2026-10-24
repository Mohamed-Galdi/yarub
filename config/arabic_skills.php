<?php

return [
    // Explicit opt-in, in addition to the environment and local database guard.
    'allow_development_import' => (bool) env('ARABIC_SKILLS_ALLOW_DEVELOPMENT_IMPORT', false),
    // Explicit owner-approved existing local database; never accepted in testing or production.
    'existing_local_database' => env('ARABIC_SKILLS_EXISTING_LOCAL_DATABASE'),
    // Independent production opt-in. The command also requires target, digest and confirmation.
    'allow_production_import' => (bool) env('ARABIC_SKILLS_ALLOW_PRODUCTION_IMPORT', false),
];
