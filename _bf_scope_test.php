<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\BriefingService;
use App\Services\BriefingGenerationService;
use App\Repositories\BriefingConfigRepository;
use App\Repositories\BriefingPageRepository;
use Illuminate\Support\Facades\DB;

$svc = app(BriefingService::class);
$gen = app(BriefingGenerationService::class);
$repo = app(BriefingConfigRepository::class);
$pageRepo = app(BriefingPageRepository::class);

// check categories + feeds for user 1
$cats = DB::table('feed_subs')->where('user_id',1)->where('status',1)->distinct()->pluck('category_id');
echo "user1 subscribed categories: " . json_encode($cats) . "\n";

$scopes = array('all','feeds','exclude_feeds','by_category');
$created = array();
foreach ($scopes as $scope) {
    $data = array(
        'name'=>'测试-'.$scope, 'pull_hours'=>6, 'schedule_time'=>'08:00',
        'scope'=>$scope,
        'feed_ids'=>$scope==='feeds'?array(49,51):($scope==='exclude_feeds'?array(49):array()),
        'category_ids'=>$scope==='by_category'?$cats->take(2)->all():array(),
        'supplement'=>null, 'enabled'=>true,
    );
    $cfg = $svc->saveConfigByUserId(1, $data);
    $created[] = $cfg->id;
    $createdScopeList[] = array('id'=>$cfg->id,'scope'=>$scope,'feeds'=>(array)$cfg->feed_ids_json,'cats'=>(array)$cfg->category_ids_json);
    // generation should not throw
    $res = $gen->generateForConfig($cfg->id, 1);
    echo "scope=$scope cfg={$cfg->id} generate=" . ($res['status']??'?') . "\n";
    // cleanup page
    if (!empty($res['page_id'])) { $p=$pageRepo->findById($res['page_id']); if($p) $p->delete(); }
}

echo "saved configs: " . json_encode($createdScopeList) . "\n";
// cleanup configs
foreach ($created as $id) { $repo->destroy($id, 1); }
echo "cleanup configs count=" . App\Models\BriefingConfig::count() . "\n";
echo "ALL SCOPE TEST OK\n";
