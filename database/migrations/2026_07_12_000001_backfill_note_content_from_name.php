<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillNoteContentFromName extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('notes', 'content')) {
            return;
        }

        // Before notes had a separate title, the note body was stored in name.
        // Only move rows without content so that already migrated notes are not overwritten.
        DB::table('notes')
            ->where(function ($query) {
                $query->whereNull('content')->orWhere('content', '');
            })
            ->whereNotNull('name')
            ->where('name', '<>', '')
            ->update(array(
                'content' => DB::raw('name'),
                'name' => '',
            ));
    }

    public function down()
    {
        // This data move is intentionally irreversible: the original title/body
        // distinction did not exist, so it cannot be restored without ambiguity.
    }
}
