<?php

namespace App\Http\Controllers;

use App\Models\LlmProvider;
use App\Models\LlmModel;
use App\Models\LlmProviderCredential;
use App\Services\LlmProviderService;
use App\Services\LlmModelService;
use App\Services\LlmProviderCredentialService;
use App\Services\LlmUsageLogService;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use App\Http\Utils\ResponseDataUtil;

/**
 * LLM管理控制器
 *
 * @author edison.an
 */
class LlmController extends Controller
{
    protected $providerService;
    protected $modelService;
    protected $credentialService;
    protected $usageLogService;

    public function __construct(
        LlmProviderService $providerService,
        LlmModelService $modelService,
        LlmProviderCredentialService $credentialService,
        LlmUsageLogService $usageLogService
    ) {
        $this->middleware('auth');
        
        $this->providerService = $providerService;
        $this->modelService = $modelService;
        $this->credentialService = $credentialService;
        $this->usageLogService = $usageLogService;
    }

    /**
     * 获取所有LLM供应商
     */
    public function getProviders(Request $request)
    {
        $userId = auth()->id();
        $providers = $this->providerService->getUserAvailableProviders($userId);
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'providers' => $providers
        ]));
    }

    /**
     * 获取所有LLM模型
     */
    public function getModels(Request $request)
    {
        $providerId = $request->input('provider_id');
        $userId = auth()->id();
        
        if ($providerId) {
            $models = $this->modelService->getModelsByProviderId($providerId);
        } else {
            $models = $this->modelService->getUserAvailableModels($userId);
        }
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'models' => $models
        ]));
    }

    /**
     * 获取所有凭据
     */
    public function getCredentials(Request $request)
    {
        $providerId = $request->input('provider_id');
        $userId = auth()->id();
        
        if ($providerId) {
            // 如果指定了供应商ID，获取该供应商下用户可用的凭据
            $credentials = $this->credentialService->getCredentialsByProviderId($providerId)
                ->where(function($q) use ($userId) {
                    $q->where('user_id', $userId)
                       ->orWhereNull('user_id');
                });
        } else {
            $credentials = $this->credentialService->getUserAvailableCredentials($userId);
        }
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'credentials' => $credentials
        ]));
    }

    /**
     * 获取使用记录统计
     */
    public function getUsageStats(Request $request)
    {
        $filters = [
            'provider_id' => $request->input('provider_id'),
            'model_id' => $request->input('model_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date')
        ];

        $stats = $this->usageLogService->getUsageStatistics($filters);
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'stats' => $stats
        ]));
    }

    /**
     * 获取单个LLM供应商
     */
    public function getProvider(Request $request, $id)
    {
        $userId = auth()->id();
        $provider = $this->providerService->getProviderByIdForUser($id, $userId);
        
        if (!$provider) {
            return $this->jsonResponse($request, ResponseDataUtil::genErrSucc('供应商不存在'));
        }
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'provider' => $provider
        ]));
    }

    /**
     * 创建或更新LLM供应商
     */
    public function saveProvider(Request $request, $id = null)
    {
        $this->validate($request, [
            'name' => 'required|max:100',
            'slug' => $id ? 'required|max:50' : 'required|max:50|unique:llm_providers,slug',
            'api_type' => 'required',
        ]);
        
        $data = $request->only([
            'name', 'slug', 'description', 'base_url', 'api_type',
            'is_active', 'priority', 'config_schema', 'rate_limit_per_minute', 'concurrent_limit'
        ]);
        
        // 确保user_id是当前用户
        $data['user_id'] = auth()->id();
        
        if ($id) {
            // 更新现有供应商
            $provider = $this->providerService->updateProvider($id, $data);
            if (!$provider) {
                return $this->jsonResponse($request, ResponseDataUtil::genErrSucc('供应商不存在'));
            }
        } else {
            // 创建新供应商
            $provider = $this->providerService->createProvider($data);
        }
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'provider' => $provider
        ]));
    }

    /**
     * 删除LLM供应商
     */
    public function deleteProvider(Request $request, $id)
    {
        $userId = auth()->id();
        $provider = LlmProvider::where('id', $id)->where('user_id', $userId)->first();
        
        if (!$provider) {
            return $this->jsonResponse($request, ResponseDataUtil::genErrSucc('供应商不存在'));
        }
        
        $this->providerService->deleteProvider($id);
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    /**
     * 获取单个LLM模型
     */
    public function getModel(Request $request, $id)
    {
        $userId = auth()->id();
        $model = $this->modelService->getModelByIdForUser($id, $userId);
        
        if (!$model) {
            return $this->jsonResponse($request, ResponseDataUtil::genErrSucc('模型不存在'));
        }
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'model' => $model
        ]));
    }

    /**
     * 创建或更新LLM模型
     */
    public function saveModel(Request $request, $id = null)
    {
        $this->validate($request, [
            'provider_id' => 'required|exists:llm_providers,id',
            'name' => 'required|max:100',
            'model_type' => 'required',
        ]);
        
        $data = $request->only([
            'provider_id', 'name', 'display_name', 'model_type',
            'context_length', 'max_tokens', 'input_price_per_1k', 'output_price_per_1k',
            'is_active', 'capabilities', 'sort_order'
        ]);
        
        // 确保user_id是当前用户
        $data['user_id'] = auth()->id();
        
        if ($id) {
            // 更新现有模型
            $model = $this->modelService->updateModel($id, $data);
            if (!$model) {
                return $this->jsonResponse($request, ResponseDataUtil::genErrSucc('模型不存在'));
            }
        } else {
            // 创建新模型
            $model = $this->modelService->createModel($data);
        }
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'model' => $model
        ]));
    }

    /**
     * 删除LLM模型
     */
    public function deleteModel(Request $request, $id)
    {
        $userId = auth()->id();
        $model = LlmModel::where('id', $id)->where('user_id', $userId)->first();
        
        if (!$model) {
            return $this->jsonResponse($request, ResponseDataUtil::genErrSucc('模型不存在'));
        }
        
        $this->modelService->deleteModel($id);
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    /**
     * 获取单个凭据
     */
    public function getCredential(Request $request, $id)
    {
        $userId = auth()->id();
        $credential = $this->credentialService->getCredentialByIdForUser($id, $userId);
        
        if (!$credential) {
            return $this->jsonResponse($request, ResponseDataUtil::genErrSucc('凭据不存在'));
        }
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'credential' => $credential
        ]));
    }

    /**
     * 创建或更新凭据
     */
    public function saveCredential(Request $request, $id = null)
    {
        if ($id) {
            // 更新凭据时，API Key是可选的（如果为空则保持原值）
            $this->validate($request, [
                'provider_id' => 'required|exists:llm_providers,id',
                'name' => 'required|max:100',
            ]);
            
            $data = $request->only([
                'provider_id', 'name', 'config',
                'is_default', 'quota_limit', 'is_active'
            ]);
            
            // 如果提供了API Key，则更新它
            if ($request->filled('api_key')) {
                $data['api_key'] = $request->input('api_key');
            }
        } else {
            // 创建凭据时，API Key是必需的
            $this->validate($request, [
                'provider_id' => 'required|exists:llm_providers,id',
                'name' => 'required|max:100',
                'api_key' => 'required',
            ]);
            
            $data = $request->only([
                'provider_id', 'name', 'api_key', 'config',
                'is_default', 'quota_limit', 'is_active'
            ]);
        }
        
        // 确保user_id是当前用户
        $data['user_id'] = auth()->id();
        
        // 如果设置为默认凭据，取消同供应商下其他默认凭据
        if (!empty($data['is_default'])) {
            $this->credentialService->getCredentialsByProviderId($data['provider_id'], false)
                ->where('is_default', true)
                ->where('user_id', auth()->id())
                ->each(function ($cred) {
                    $cred->update(['is_default' => false]);
                });
        }
        
        if ($id) {
            // 更新现有凭据
            $credential = $this->credentialService->updateCredential($id, $data);
            if (!$credential) {
                return $this->jsonResponse($request, ResponseDataUtil::genErrSucc('凭据不存在'));
            }
        } else {
            // 创建新凭据
            $credential = $this->credentialService->createCredential($data);
        }
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'credential' => $credential
        ]));
    }

    /**
     * 删除凭据
     */
    public function deleteCredential(Request $request, $id)
    {
        $userId = auth()->id();
        $credential = LlmProviderCredential::where('id', $id)->where('user_id', $userId)->first();
        
        if (!$credential) {
            return $this->jsonResponse($request, ResponseDataUtil::genErrSucc('凭据不存在'));
        }
        
        $this->credentialService->deleteCredential($id);
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }
}