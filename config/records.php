<?php

return [
    // Archived records stay recoverable for three months before the daily purge.
    'archived_retention_days' => max(30, (int) env('ARCHIVED_RECORD_RETENTION_DAYS', 90)),
];
