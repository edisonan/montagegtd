<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
use Storage;

/**
 * 备份到七牛云
 *
 * $schedule->command ( 'backup2qiniu', array ( env ( 'TASK_SQL_FILE_PATH' ) ) )->dailyAt ( '18:00' );
 * $schedule->command ( 'backup2qiniu', array ( env ( 'WWW_SQL_FILE_PATH' ) ) )->dailyAt ( '18:00' );
 *
 * @author edison.an
 *
 */
class Backup2Qiniu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup2qiniu {file_path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup File To Qiniu!';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file_path = $this->argument('file_path');
        $path = $file_path . '/' . date('Ymd') . '.sql.tar.gz';

        if (file_exists($path)) {
            try {
                Storage::disk('qiniu')->put(basename($path), fopen($path, 'r+'));
                Log::info('upload to qiniu success :' . $path);
            } catch (Exception $e) {
                Log::error('backup qiniuyun wrong, upload to qiniu :' . $path . '|' . $e->getMessage());
            }
        } else {
            Log::error('backup qiniuyun wrong, path not exist :' . $path);
        }
    }
}
