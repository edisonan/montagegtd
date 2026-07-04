<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MergeStudyPlansIntoPlans extends Migration
{
    public function up()
    {
        if (Schema::hasTable('plans')) {
            Schema::table('plans', function (Blueprint $table) {
                if (!Schema::hasColumn('plans', 'plan_type')) {
                    $table->string('plan_type', 32)->default('general')->index();
                }
                if (!Schema::hasColumn('plans', 'content')) {
                    $table->text('content')->nullable();
                }
                if (!Schema::hasColumn('plans', 'start_time')) {
                    $table->dateTime('start_time')->nullable()->index();
                }
                if (!Schema::hasColumn('plans', 'repeat_type')) {
                    $table->string('repeat_type', 32)->default('none')->index();
                }
                if (!Schema::hasColumn('plans', 'repeat_days')) {
                    $table->string('repeat_days', 64)->nullable();
                }
                if (!Schema::hasColumn('plans', 'repeat_meta')) {
                    $table->text('repeat_meta')->nullable();
                }
                if (!Schema::hasColumn('plans', 'sp_points')) {
                    $table->integer('sp_points')->default(0);
                }
                if (!Schema::hasColumn('plans', 'last_generated_date')) {
                    $table->date('last_generated_date')->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('plans') && Schema::hasTable('study_plans')) {
            $rows = DB::table('study_plans')->orderBy('id', 'asc')->get();
            foreach ($rows as $r) {
                $exists = DB::table('plans')
                    ->where('user_id', (int)$r->user_id)
                    ->where('plan_type', 'study')
                    ->where('name', (string)$r->name)
                    ->where('start_time', (string)$r->start_time)
                    ->first();
                if ($exists) {
                    continue;
                }
                DB::table('plans')->insert(array(
                    'user_id' => (int)$r->user_id,
                    'name' => (string)$r->name,
                    'status' => (int)$r->status,
                    'plan_type' => 'study',
                    'content' => (string)($r->content ?: ''),
                    'start_time' => (string)$r->start_time,
                    'repeat_type' => (string)($r->repeat_type ?: 'none'),
                    'repeat_days' => (string)($r->repeat_days ?: ''),
                    'repeat_meta' => (string)($r->repeat_meta ?: ''),
                    'sp_points' => (int)($r->sp_points ?: 0),
                    'last_generated_date' => !empty($r->last_generated_date) ? (string)$r->last_generated_date : null,
                    'created_at' => !empty($r->created_at) ? (string)$r->created_at : date('Y-m-d H:i:s'),
                    'updated_at' => !empty($r->updated_at) ? (string)$r->updated_at : date('Y-m-d H:i:s'),
                ));
            }

            Schema::drop('study_plans');
        }
    }

    public function down()
    {
        // Keep merged columns for compatibility.
    }
}
