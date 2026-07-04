# LLM管理功能安装说明

## 1. 数据库表创建

### 方法一：使用SQL文件（推荐，无需composer依赖）

```bash
# 运行数据库表创建脚本
php setup_llm_tables.php
```

### 方法二：使用Laravel迁移（需要先安装依赖）

```bash
# 安装项目依赖
composer install

# 执行迁移
php artisan migrate
```

## 2. 菜单项添加

如果使用Laravel Admin，需要添加菜单项：

```bash
# 添加LLM管理菜单项到后台
php artisan llm:add-menu-items
```

## 3. 功能概述

系统已创建以下LLM管理功能：

### LLM供应商管理 (`/admin/llm-providers`)
- 供应商信息管理
- API类型配置
- 速率限制设置
- 配置Schema定义
- 支持用户级和全局供应商

### LLM模型管理 (`/admin/llm-models`)
- 模型信息管理
- 模型类型分类（chat/completion/embedding/image）
- 价格配置
- 能力配置
- 支持用户级和全局模型

### LLM凭据管理 (`/admin/llm-provider-credentials`)
- API Key管理
- 多账号支持
- 配额管理
- 默认凭据设置
- 支持用户级和全局凭据

### LLM使用记录管理 (`/admin/llm-usage-logs`)
- 调用记录追踪
- Token统计
- 成本计算
- 性能监控

## 4. 用户级数据功能

系统支持用户级LLM资源管理，用户可以创建和管理自己的供应商、模型和凭据：

- 用户创建的资源会自动关联到当前用户
- 在API接口中，系统优先返回用户自己的资源，其次返回全局资源
- 在后台管理中，普通用户只能看到自己的资源（管理员可查看所有资源）

## 5. API接口

系统提供以下API接口：

```
GET /llm/providers - 获取用户可用的LLM供应商（用户自己的 + 全局的）
GET /llm/models - 获取用户可用的LLM模型（用户自己的 + 全局的）
GET /llm/credentials - 获取用户可用的凭据（用户自己的 + 全局的）
GET /llm/usage-stats - 获取使用统计
```

## 6. 代码结构

- **Models**: [LlmProvider](app/Models/LlmProvider.php), [LlmModel](app/Models/LlmModel.php), [LlmProviderCredential](app/Models/LlmProviderCredential.php), [LlmUsageLog](app/Models/LlmUsageLog.php)
- **Repositories**: [LlmProviderRepository](app/Repositories/LlmProviderRepository.php), [LlmModelRepository](app/Repositories/LlmModelRepository.php), [LlmProviderCredentialRepository](app/Repositories/LlmProviderCredentialRepository.php), [LlmUsageLogRepository](app/Repositories/LlmUsageLogRepository.php)
- **Services**: [LlmProviderService](app/Services/LlmProviderService.php), [LlmModelService](app/Services/LlmModelService.php), [LlmProviderCredentialService](app/Services/LlmProviderCredentialService.php), [LlmUsageLogService](app/Services/LlmUsageLogService.php)
- **Controllers**: [LlmProviderController](app/Admin/Controllers/LlmProviderController.php), [LlmModelController](app/Admin/Controllers/LlmModelController.php), [LlmProviderCredentialController](app/Admin/Controllers/LlmProviderCredentialController.php), [LlmUsageLogController](app/Admin/Controllers/LlmUsageLogController.php)
- **Routes**: [admin routes](app/Admin/routes.php), [web routes](routes/web.php)

## 7. 依赖注册

服务已通过 [LlmServiceProvider](app/Providers/LlmServiceProvider.php) 自动注册。

## 8. 使用说明

1. 在后台管理界面的"LLM管理"菜单下配置供应商信息
2. 添加对应的模型信息
3. 配置API凭据
4. 系统会自动记录API调用情况
5. 通过使用记录页面查看统计信息
6. 用户创建的资源会自动关联到当前用户，优先使用用户级资源