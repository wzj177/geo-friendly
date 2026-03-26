# 通用后台管理系统集成方案

为不支持 CMS 的项目设计符合 GEO 规范的后台管理系统。

## 适用场景

- ✅ SaaS 应用 - 多租户 GEO 文件生成
- ✅ 企业后台 - 静态/半静态页面管理
- ✅ 多语言站点 - 不同语言的 GEO 文件
- ✅ 微服务架构 - 各服务独立管理文档

## 核心流程

```
后台编辑器 (Markdown)
    ↓
数据库 (结构化内容)
    ↓
Geo-Friendly 包
    ↓
GEO 文件 (llms.txt, sitemap.xml)
    ↓
AI 引擎 (ChatGPT, Claude)
```

## 数据库设计

### 内容表

```sql
CREATE TABLE contents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT UNSIGNED NOT NULL,

    -- 基本信息
    slug VARCHAR(255) NOT NULL,           -- URL 路径
    url VARCHAR(512) NOT NULL,            -- 完整 URL

    -- GEO 必需字段
    title VARCHAR(255) NOT NULL,          -- 页面标题
    description TEXT,                     -- AI 理解用描述 (9-10个词)
    content MEDIUMTEXT,                   -- Markdown 内容

    -- 分类
    category VARCHAR(100),
    tags JSON,

    -- 状态
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at DATETIME,

    -- 索引
    INDEX idx_site_status (site_id, status),
    UNIQUE KEY uk_site_slug (site_id, slug)
);
```

### 站点配置表

```sql
CREATE TABLE site_configs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT UNSIGNED NOT NULL UNIQUE,

    site_name VARCHAR(255) NOT NULL,
    site_url VARCHAR(512) NOT NULL,
    site_description TEXT,

    geo_config JSON NOT NULL,             -- Geo-Friendly 配置
    auto_generate BOOLEAN DEFAULT FALSE,  -- 是否自动生成

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## 代码实现

### Laravel 集成示例

```php
<?php

namespace App\Service;

use GeoFriendly\GeoFriendly;

class GeoGenerator
{
    /**
     * 为指定站点生成 GEO 文件
     */
    public function generateForSite(int $siteId): array
    {
        $site = SiteConfig::findOrFail($siteId);

        // 从数据库获取已发布内容
        $contents = Content::where('site_id', $siteId)
            ->where('status', 'published')
            ->get()
            ->map(function ($item) {
                return [
                    'title' => $item->title,
                    'url' => $item->url,
                    'content' => $item->content,
                    'description' => $item->description,
                    'category' => $item->category,
                    'tags' => $item->tags ?? [],
                ];
            })
            ->toArray();

        // 配置 Geo-Friendly
        $config = [
            'title' => $site->site_name,
            'url' => rtrim($site->site_url, '/'),
            'description' => $site->site_description,
            'outDir' => storage_path("geo/site_{$siteId}"),
            'contents' => $contents,  // 直接传入内容数组
        ];

        // 生成 GEO 文件
        $geo = new GeoFriendly($config);
        [$generated, $errors] = $geo->generate();

        return [
            'success' => empty($errors),
            'generated' => $generated,
            'errors' => $errors,
        ];
    }
}
```

### 控制器

```php
<?php

namespace App\Http\Controllers;

use App\Service\GeoGenerator;

class GeoController extends Controller
{
    public function generate(Request $request)
    {
        $siteId = $request->user()->current_site_id;

        $result = app(GeoGenerator::class)->generateForSite($siteId);

        return response()->json($result);
    }

    /**
     * 获取生成的 GEO 文件内容
     */
    public function getFile(Request $request, string $filename)
    {
        $siteId = $request->user()->current_site_id;
        $path = storage_path("geo/site_{$siteId}/{$filename}");

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response(file_get_contents($path))
            ->header('Content-Type', $this->getContentType($filename));
    }

    private function getContentType(string $filename): string
    {
        $types = [
            'llms.txt' => 'text/plain; charset=utf-8',
            'llms-full.txt' => 'text/plain; charset=utf-8',
            'sitemap.xml' => 'application/xml',
            'robots.txt' => 'text/plain; charset=utf-8',
            'docs.json' => 'application/json',
            'ai-index.json' => 'application/json',
            'schema.json' => 'application/ld+json',
        ];

        return $types[$filename] ?? 'text/plain';
    }
}
```

### 路由

```php
// routes/web.php

Route::middleware('auth')->group(function () {
    Route::post('/api/geo/generate', [GeoController::class, 'generate']);
    Route::get('/api/geo/files/{filename}', [GeoController::class, 'getFile']);
});
```

## Nginx 配置 (代理模式)

将 GEO 文件请求代理到后端 API：

```nginx
server {
    listen 80;
    server_name example.com;

    # GEO 文件代理到后端
    location /llms.txt {
        proxy_pass http://backend/api/geo/files/llms.txt;
        proxy_set_header Content-Type "text/plain; charset=utf-8";
    }

    location /sitemap.xml {
        proxy_pass http://backend/api/geo/files/sitemap.xml;
        proxy_set_header Content-Type "application/xml";
    }

    location /docs.json {
        proxy_pass http://backend/api/geo/files/docs.json;
        proxy_set_header Content-Type "application/json";
    }

    # 其他请求
    location / {
        proxy_pass http://backend;
    }
}
```

## API 响应示例

### 生成 GEO 文件

```http
POST /api/geo/generate

Response 200:
{
  "success": true,
  "generated": ["llms.txt", "sitemap.xml", "docs.json"],
  "errors": []
}
```

### 获取文件

```http
GET /api/geo/files/llms.txt

Response 200:
# My Site
Title: My Site
...
```

## 内容规范

### 标题规范
- 长度：3-4 个词 (中文) 或 5-8 个单词 (英文)
- 格式：`主标题 - 副标题`
- 示例：`快速开始 - VitePress 文档`

### 描述规范
- 长度：9-10 个词 (中文) 或 15-20 个单词 (英文)
- 包含关键词和核心价值
- 示例：`5分钟快速上手 VitePress 静态站点生成器`

### URL 规范
- 使用小写字母和连字符
- 示例：`/getting-started`

## 部署方案

### 1. 代理模式
前端请求 → Nginx → 后端 API → 返回 GEO 文件

**优点**: 实时生成，无需同步
**缺点**: 每次请求都生成

### 2. 同步模式
定时任务 → 生成文件 → 上传到 CDN

**优点**: 性能好，CDN 加速
**缺点**: 有延迟

### 3. 混合模式 (推荐)
缓存优先，过期后重新生成

```php
public function getFile(string $filename)
{
    $cacheKey = "geo:{$filename}";

    // 尝试从缓存获取
    if ($cached = Cache::get($cacheKey)) {
        return response($cached['content'])
            ->header('Content-Type', $cached['type']);
    }

    // 生成并缓存
    $result = $this->generateForSite($siteId);
    Cache::put($cacheKey, $result['files'][$filename], 3600);

    return response($result['files'][$filename])
        ->header('Content-Type', $this->getContentType($filename));
}
```

## 最佳实践

1. **按需生成**: 只在内容更新时重新生成
2. **队列处理**: 大量内容时使用队列
3. **缓存策略**: 生成文件后缓存 1-24 小时
4. **监控**: 记录生成日志和错误

```php
Log::info('GEO files generated', [
    'site_id' => $siteId,
    'files' => $generated,
    'duration' => $duration,
]);
```

## 内容数组格式

直接传入内容数组时，格式如下：

```php
$contents = [
    [
        'title' => '页面标题',           // 必需
        'url' => '/page-path',          // 必需 (如 /getting-started)
        'content' => '# Markdown内容',   // 必需
        'description' => 'AI友好的描述',  // 可选 (9-10个词)
        'category' => 'guide',           // 可选
        'tags' => ['tag1', 'tag2'],     // 可选
    ],
    // ...
];
```

## 总结

通用后台方案核心：

1. **数据库存储**: 结构化存储符合 GEO 规范的内容
2. **内容数组**: 直接从数据库传入，无需临时文件
3. **灵活生成**: 按需生成标准 GEO 文件
4. **多种部署**: 支持代理、同步、混合模式
