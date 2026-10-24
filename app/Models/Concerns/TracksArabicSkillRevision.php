<?php

namespace App\Models\Concerns;

trait TracksArabicSkillRevision
{
    protected static function bootTracksArabicSkillRevision(): void
    {
        static::updating(function ($record) {
            $changed = array_diff(array_keys($record->getDirty()), ['updated_at', 'import_checksum', 'import_version', 'content_revision']);
            if ($changed !== []) {
                $record->content_revision = $record->getOriginal('content_revision') + 1;
            }
        });
    }
}
