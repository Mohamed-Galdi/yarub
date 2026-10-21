<?php

return [
    // Explicit opt-in, in addition to the environment and local database guard.
    'allow_development_import' => (bool) env('QUDRAT_ALLOW_DEVELOPMENT_IMPORT', false),
];
