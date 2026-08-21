<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Monolog\Processor\UidProcessor;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
		// $monolog = Log::getMonolog();
		// $monolog->pushProcessor(new UidProcessor());
		if (config('app.debug')) {
			\DB::listen ( function ($query) {
				Log::info ( $query->sql );
			} );
		}

        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        // laravel-admin 操作日志会把完整请求 input（JSON）存进 admin_operation_log.input（TEXT，最大约 64KB）。
        // 当请求含超大字段（如整份 HTML content）时，json_encode 结果超过 64KB，
        // MySQL 会抛 SQLSTATE[22001] "Data too long for column 'input'"，导致整条请求 5xx。
        // 这里在写入前按字节截断超长 input，避免落库失败。
        \Encore\Admin\Auth\Database\OperationLog::creating(function ($log) {
            $maxBytes = 60000;
            $input    = (string) $log->input;
            if (strlen($input) > $maxBytes) {
                $log->input = mb_strcut($input, 0, $maxBytes, 'UTF-8');
            }
        });
	}
	
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		//
	}
}
