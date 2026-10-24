<?php

namespace App\Services\ArabicSkills;

// Request-scoped approval: never stored in configuration or shared service state.
final class ProductionImportApproval
{
    public function __construct(public readonly string $database, public readonly string $digest) {}
}
