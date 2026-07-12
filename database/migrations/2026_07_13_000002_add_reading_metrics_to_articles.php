<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddReadingMetricsToArticles extends Migration
{
    public function up()
    {
        // The legacy table has a zero timestamp default which strict MySQL
        // rejects while altering the table. This only changes the migration
        // connection and does not change the server-wide SQL mode.
        DB::statement("SET SESSION sql_mode = ''");
        DB::statement(
            "ALTER TABLE articles
             ADD COLUMN word_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER content,
             ADD COLUMN estimated_read_minutes INT UNSIGNED NOT NULL DEFAULT 1 AFTER word_count,
             ADD INDEX articles_word_count_index (word_count),
             ADD INDEX articles_estimated_read_minutes_index (estimated_read_minutes)"
        );

        DB::table('articles')->orderBy('id')->chunk(500, function ($articles) {
            foreach ($articles as $article) {
                $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$article->content)));
                $wordCount = function_exists('mb_strlen')
                    ? mb_strlen($plainText, 'UTF-8')
                    : strlen($plainText);

                DB::table('articles')->where('id', $article->id)->update(array(
                    'word_count' => (int)$wordCount,
                    'estimated_read_minutes' => max(1, (int)ceil($wordCount / 320)),
                ));
            }
        });
    }

    public function down()
    {
        DB::statement(
            'ALTER TABLE articles DROP COLUMN word_count, DROP COLUMN estimated_read_minutes'
        );
    }
}
