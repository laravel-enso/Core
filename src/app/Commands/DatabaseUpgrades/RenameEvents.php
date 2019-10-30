<?php

namespace LaravelEnso\Core\app\Commands\DatabaseUpgrades;

use Illuminate\Support\Facades\Schema;

class RenameEvents extends DatabaseUpgrade
{
    protected function isMigrated()
    {
        return Schema::hasTable('calendar_events');
    }

    public function migrateTable()
    {
        Schema::rename('events', 'calendar_events');
    }
}
