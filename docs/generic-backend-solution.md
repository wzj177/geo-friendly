# 通用后台管理系统集成方案

本文档详细说明如何为不支持 CMS 的项目设计一个符合 GEO（Generative Engine Optimization）规范的后台管理系统。

## 方案概述

### 核心理念

**后台编辑器 → Geo-Friendly 包 → GEO 文件 → AI 引擎**

1. **后台编辑**: 用 Markdown 编辑器维护内容
2. **结构化存储**: 按照规范存储内容元数据
3. **文件生成**: 使用 Geo-Friendly 包生成标准 GEO 文件
4. **AI 发现**: ChatGPT、Claude、Perplexity 等引擎发现和引用

### 适用场景

- ✅ SaaS 应用 - 需要为每个租户生成 SEO 文件
- ✅ 企业后台 - 需要维护大量静态/半静态页面
- ✅ 多语言站点 - 需要为不同语言生成对应的 GEO 文件
- ✅ 微服务架构 - 各服务独立管理自己的文档
- ✅ 内部系统 - 外部不可访问，需要通过 API 生成 GEO

## 系统架构

```
┌─────────────────────────────────────────────────────────────┐
│                    后台管理系统                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │  内容管理    │  │  元数据管理  │  │  URL 管理    │     │
│  │  (Markdown)  │  │  (SEO 字段)  │  │  (路由映射)  │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ 调用 Geo-Friendly 包
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Geo-Friendly 包                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │ 内容读取     │  │ GEO 文件生成 │  │ 多格式输出   │     │
│  │ (Markdown)   │  │ (标准规范)   │  │ (txt/json/xml)│     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                      输出文件                                │
│  llms.txt  |  llms-full.txt  |  docs.json  |  sitemap.xml  │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    AI 引擎                                   │
│  ChatGPT  |  Claude  |  Perplexity  |  其他 LLM           │
└─────────────────────────────────────────────────────────────┘
```

## 数据库设计

### 内容表 (contents)

```sql
CREATE TABLE contents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT UNSIGNED NOT NULL,                    -- 站点/租户 ID
    type ENUM('page', 'article', 'api', 'guide') NOT NULL,

    -- 基本信息
    slug VARCHAR(255) NOT NULL,                          -- URL 路径
    url VARCHAR(512) NOT NULL,                           -- 完整 URL

    -- GEO 必需字段
    title VARCHAR(255) NOT NULL,                         -- 页面标题
    description TEXT,                                    -- 页面描述（AI 理解用）
    keywords VARCHAR(512),                               -- 关键词（可选）

    -- 内容
    content MEDIUMTEXT,                                  -- Markdown 内容
    html_content MEDIUMTEXT,                             -- 渲染后的 HTML（可选）

    -- 分类和标签
    category VARCHAR(100),
    tags JSON,

    -- SEO 元数据
    meta_title VARCHAR(255),                             -- SEO 标题
    meta_description VARCHAR(512),                       -- SEO 描述
    og_title VARCHAR(255),                               -- Open Graph 标题
    og_description VARCHAR(512),                         -- Open Graph 描述
    og_image VARCHAR(512),                               -- Open Graph 图片

    -- 多语言支持
    language CHAR(2) DEFAULT 'zh',                       -- 语言代码
    translation_id BIGINT UNSIGNED,                      -- 翻译关联 ID

    -- 状态
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at DATETIME,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- 索引
    INDEX idx_site_status (site_id, status),
    INDEX idx_site_type (site_id, type),
    INDEX idx_language (language),
    UNIQUE KEY uk_site_slug (site_id, slug)
);
```

### 站点配置表 (site_configs)

```sql
CREATE TABLE site_configs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT UNSIGNED NOT NULL UNIQUE,

    -- 基本信息
    site_name VARCHAR(255) NOT NULL,
    site_url VARCHAR(512) NOT NULL,
    site_description TEXT,

    -- Geo-Friendly 配置
    geo_config JSON NOT NULL,                             -- 存储完整配置

    -- 生成配置
    auto_generate BOOLEAN DEFAULT FALSE,                  -- 是否自动生成
    generate_frequency ENUM('manual', 'hourly', 'daily', 'weekly') DEFAULT 'manual',
    last_generated_at DATETIME,

    -- OpenAI 配置（可选）
    openai_api_key VARCHAR(255),
    openai_enabled BOOLEAN DEFAULT FALSE,

    -- Firecrawl 配置（可选）
    firecrawl_api_key VARCHAR(255),
    firecrawl_enabled BOOLEAN DEFAULT FALSE,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### URL 映射表 (url_mappings)

```sql
CREATE TABLE url_mappings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT UNSIGNED NOT NULL,
    content_id BIGINT UNSIGNED NOT NULL,

    -- URL 信息
    pattern VARCHAR(512) NOT NULL,                       -- URL 模式
    priority DECIMAL(3,2) DEFAULT 0.5,                   -- 优先级 0.0-1.0
    change_freq ENUM('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never') DEFAULT 'weekly',

    -- 最后修改时间
    last_modified DATETIME,

    INDEX idx_site_priority (site_id, priority),
    INDEX idx_content (content_id)
);
```

## 后台界面设计

### 内容编辑界面

```markdown
┌────────────────────────────────────────────────────────────┐
│  内容管理 > 编辑页面                                        │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  基本信息                                                  │
│  ┌─────────────────┐  ┌─────────────────┐                │
│  │ 标题 *          │  │ URL 路径 *      │                │
│  └─────────────────┘  └─────────────────┘                │
│                                                            │
│  SEO 信息                                                  │
│  ┌─────────────────────────────────────────────────┐      │
│  │ 描述 (用于 AI 理解，9-10 个词)                   │      │
│  └─────────────────────────────────────────────────┘      │
│  ┌─────────────────┐  ┌─────────────────┐                │
│  │ 关键词          │  │ 分类            │                │
│  └─────────────────┘  └─────────────────┘                │
│                                                            │
│  内容编辑                                                  │
│  ┌─────────────────────────────────────────────────┐      │
│  │                                                 │      │
│  │  Markdown 编辑器 (支持实时预览)                   │      │
│  │                                                 │      │
│  └─────────────────────────────────────────────────┘      │
│                                                            │
│  高级选项                                                  │
│  ┌─────────────────┐  ┌─────────────────┐                │
│  │ 优先级 (0-1)    │  │ 更新频率        │                │
│  └─────────────────┘  └─────────────────┘                │
│                                                            │
│  ┌─────────────────┐  ┌─────────────────┐                │
│  │ Open Graph 标题 │  │ OG 图片         │                │
│  └─────────────────┘  └─────────────────┘                │
│                                                            │
│  [保存草稿]  [预览]  [发布并生成 GEO]                      │
└────────────────────────────────────────────────────────────┘
```

### GEO 生成配置界面

```markdown
┌────────────────────────────────────────────────────────────┐
│  设置 > GEO 配置                                            │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  站点信息                                                  │
│  ┌─────────────────────────────────────────────────┐      │
│  │ 站点名称: [我的网站]                               │      │
│  │ 站点 URL: [https://example.com]                  │      │
│  │ 描述:     [网站描述...]                           │      │
│  └─────────────────────────────────────────────────┘      │
│                                                            │
│  内容模式                                                  │
│  ◉ 本地 Markdown（从数据库读取内容）                      │
│  ○ Firecrawl 爬虫（从网站爬取）                          │
│                                                            │
│  生成器配置                                                │
│  ☑ llms.txt        (LLM 发现文件)                         │
│  ☑ llms-full.txt   (完整文档索引)                         │
│  ☑ sitemap.xml     (搜索引擎站点地图)                     │
│  ☑ docs.json       (结构化文档)                           │
│  ☑ ai-index.json   (AI 优化索引)                          │
│  ☑ schema.json     (Schema.org 结构化数据)                │
│  ☑ robots.txt      (爬虫指令)                             │
│                                                            │
│  AI 增强（可选）                                          │
│  ┌─────────────────────────────────────────────────┐      │
│  │ OpenAI API Key: [••••••••••••••••]               │      │
│  │ Model:           [gpt-4o-mini ▼]                  │      │
│  │ ☑ 启用 AI 增强                                    │      │
│  └─────────────────────────────────────────────────┘      │
│                                                            │
│  自动生成                                                  │
│  ┌─────────────────────────────────────────────────┐      │
│  │ 生成频率: [每天 ▼]                               │      │
│  │ 上次生成: 2024-03-25 10:30:00                    │      │
│  │ 下次生成: 2024-03-26 10:30:00                    │      │
│  │                                                 │      │
│  │ [立即生成] [查看生成的文件] [下载配置]            │      │
│  └─────────────────────────────────────────────────┘      │
└────────────────────────────────────────────────────────────┘
```

## 代码实现示例

### PHP 后端集成

```php
<?php
// src/Service/GeoGenerator.php

namespace App\Service;

use GeoFriendly\GeoFriendly;

class GeoGenerator
{
    private string $outputDir;

    public function __construct()
    {
        $this->outputDir = config('geo.output_dir', storage_path('geo'));
    }

    /**
     * 为指定站点生成 GEO 文件
     */
    public function generateForSite(int $siteId): array
    {
        // 1. 获取站点配置
        $site = SiteConfig::findOrFail($siteId);

        // 2. 获取已发布的内容
        $contents = Content::where('site_id', $siteId)
            ->where('status', 'published')
            ->get();

        // 3. 准备 Geo-Friendly 配置
        $config = [
            'title' => $site->site_name,
            'url' => rtrim($site->site_url, '/'),
            'description' => $site->site_description,
            'outDir' => $this->outputDir . '/site_' . $siteId,
            'contentDir' => '', // 使用内存数据
            'generators' => $site->geo_config['generators'] ?? [],
        ];

        // 添加 OpenAI 配置
        if ($site->openai_enabled && !empty($site->openai_api_key)) {
            $config['openai'] = [
                'apiKey' => $site->openai_api_key,
                'baseUrl' => 'https://api.openai.com/v1',
                'model' => 'gpt-4o-mini',
                'enabled' => true,
            ];
        }

        // 4. 将内容写入临时 markdown 文件
        $tempDir = $this->createTempMarkdownFiles($contents, $siteId);

        try {
            // 5. 使用 Geo-Friendly 生成
            $config['contentDir'] = $tempDir;
            $geo = new GeoFriendly($config);
            [$generated, $errors] = $geo->generate();

            // 6. 更新生成时间
            $site->update([
                'last_generated_at' => now(),
            ]);

            return [
                'success' => empty($errors),
                'generated' => $generated,
                'errors' => $errors,
                'output_dir' => $config['outDir'],
            ];

        } finally {
            // 7. 清理临时文件
            $this->cleanupTempFiles($tempDir);
        }
    }

    /**
     * 创建临时的 markdown 文件
     */
    private function createTempMarkdownFiles($contents, int $siteId): string
    {
        $tempDir = sys_get_temp_dir() . '/geo_site_' . $siteId . '_' . time();

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        foreach ($contents as $content) {
            // 构建文件路径
            $path = $tempDir . '/' . $content->slug . '.md';

            // 构建 frontmatter
            $frontmatter = [
                'title' => $content->title,
                'description' => $content->description,
                'url' => $content->url,
                'category' => $content->category,
                'tags' => $content->tags ?? [],
            ];

            // 写入文件
            $fileContent = "---\n";
            $fileContent .= yaml_emit($frontmatter);
            $fileContent .= "---\n\n";
            $fileContent .= $content->content;

            file_put_contents($path, $fileContent);
        }

        return $tempDir;
    }

    /**
     * 清理临时文件
     */
    private function cleanupTempFiles(string $tempDir): void
    {
        $files = glob($tempDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        if (is_dir($tempDir)) {
            rmdir($tempDir);
        }
    }

    /**
     * 生成并返回 GEO 文件内容（用于 API 响应）
     */
    public function generateAsResponse(int $siteId): array
    {
        $result = $this->generateForSite($siteId);

        if (!$result['success']) {
            return $result;
        }

        // 读取所有生成的文件
        $files = [];
        foreach ($result['generated'] as $filename) {
            $path = $result['output_dir'] . '/' . $filename;
            if (file_exists($path)) {
                $files[$filename] = file_get_contents($path);
            }
        }

        return [
            'success' => true,
            'files' => $files,
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
```

### Laravel 控制器

```php
<?php
// app/Http/Controllers/GeoController.php

namespace App\Http\Controllers;

use App\Service\GeoGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GeoController extends Controller
{
    private GeoGenerator $geoGenerator;

    public function __construct(GeoGenerator $geoGenerator)
    {
        $this->geoGenerator = $geoGenerator;
    }

    /**
     * 生成 GEO 文件
     */
    public function generate(Request $request): JsonResponse
    {
        $siteId = $request->user()->current_site_id;

        $result = $this->geoGenerator->generateForSite($siteId);

        return response()->json($result);
    }

    /**
     * 获取 GEO 文件内容（通过 API）
     */
    public function getFiles(Request $request, string $filename): JsonResponse
    {
        $siteId = $request->user()->current_site_id;

        $result = $this->geoGenerator->generateAsResponse($siteId);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        if (!isset($result['files'][$filename])) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->json([
            'filename' => $filename,
            'content' => $result['files'][$filename],
            'generated_at' => $result['generated_at'],
        ]);
    }

    /**
     * 预览 llms.txt
     */
    public function previewLlmsTxt(Request $request): JsonResponse
    {
        return $this->getFiles($request, 'llms.txt');
    }
}
```

### 路由配置

```php
// routes/web.php

Route::middleware('auth')->group(function () {
    // GEO 生成
    Route::post('/geo/generate', [GeoController::class, 'generate']);

    // GEO 文件预览
    Route::get('/geo/preview/{filename}', [GeoController::class, 'getFiles']);
    Route::get('/geo/preview/llms.txt', [GeoController::class, 'previewLlmsTxt']);
});
```

## API 接口设计

### 生成 GEO 文件

```http
POST /api/geo/generate
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "generated": [
    "llms.txt",
    "llms-full.txt",
    "sitemap.xml",
    "docs.json",
    "ai-index.json",
    "schema.json",
    "robots.txt"
  ],
  "errors": [],
  "output_dir": "/storage/geo/site_123"
}
```

### 获取单个文件

```http
GET /api/geo/files/llms.txt
Authorization: Bearer {token}

Response 200:
{
  "filename": "llms.txt",
  "content": "# My Site\n...",
  "generated_at": "2024-03-25T10:30:00Z"
}
```

### 批量获取文件

```http
GET /api/geo/files
Authorization: Bearer {token}

Response 200:
{
  "llms.txt": "# My Site\n...",
  "sitemap.xml": "<?xml...>",
  "docs.json": "{...}",
  ...
}
```

## 部署方案

### 方案 1: 代理模式

将生成的 GEO 文件通过 API 提供给前端：

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   前端      │────▶│   后端 API  │────▶│  Geo-Friendly│
│  (Nginx)    │     │  (Laravel)  │     │   (生成)    │
└─────────────┘     └─────────────┘     └─────────────┘
                            │
                            ▼
                     返回 GEO 文件内容
```

Nginx 配置：

```nginx
location /llms.txt {
    proxy_pass http://backend/api/geo/files/llms.txt;
    proxy_set_header Content-Type "text/plain; charset=utf-8";
}

location /sitemap.xml {
    proxy_pass http://backend/api/geo/files/sitemap.xml;
    proxy_set_header Content-Type "application/xml";
}
```

### 方案 2: 同步模式

定期生成文件并同步到 CDN：

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   后端      │────▶│  Geo-Friendly│────▶│    CDN      │
│  (定时任务) │     │   (生成)    │     │  (分发)     │
└─────────────┘     └─────────────┘     └─────────────┘
```

### 方案 3: 混合模式

热数据从 API 获取，冷数据使用缓存：

```php
public function getFile(string $filename)
{
    $cacheKey = "geo:{$filename}";

    // 尝试从缓存获取
    if ($cached = Cache::get($cacheKey)) {
        return response($cached['content'])
            ->header('Content-Type', $cached['type']);
    }

    // 从数据库生成
    $result = $this->geoGenerator->generateAsResponse($siteId);

    if (!isset($result['files'][$filename])) {
        abort(404);
    }

    // 缓存 1 小时
    Cache::put($cacheKey, [
        'content' => $result['files'][$filename],
        'type' => $this->getContentType($filename),
    ], 3600);

    return response($result['files'][$filename])
        ->header('Content-Type', $this->getContentType($filename));
}
```

## 前端集成示例

### React/Vue 组件

```vue
<template>
  <div class="geo-manager">
    <div class="header">
      <h2>GEO 文件管理</h2>
      <button @click="generateFiles" :disabled="generating">
        {{ generating ? '生成中...' : '生成 GEO 文件' }}
      </button>
    </div>

    <div class="files" v-if="files.length">
      <div v-for="file in files" :key="file.name" class="file-item">
        <div class="file-info">
          <span class="file-name">{{ file.name }}</span>
          <span class="file-size">{{ formatSize(file.size) }}</span>
        </div>
        <div class="file-actions">
          <button @click="preview(file.name)">预览</button>
          <button @click="download(file.name)">下载</button>
          <a :href="`/geo/${file.name}`" target="_blank">查看</a>
        </div>
      </div>
    </div>

    <div class="preview" v-if="previewFile">
      <h3>{{ previewFile.name }} 预览</h3>
      <pre>{{ previewFile.content }}</pre>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      files: [],
      generating: false,
      previewFile: null,
    };
  },
  methods: {
    async generateFiles() {
      this.generating = true;
      try {
        const response = await fetch('/api/geo/generate', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${this.token}`,
          },
        });
        const result = await response.json();

        if (result.success) {
          this.files = result.generated.map(name => ({
            name,
            size: 0, // 需要额外获取
          }));
        }
      } finally {
        this.generating = false;
      }
    },

    async preview(filename) {
      const response = await fetch(`/api/geo/files/${filename}`, {
        headers: {
          'Authorization': `Bearer ${this.token}`,
        },
      });
      const result = await response.json();

      this.previewFile = {
        name: filename,
        content: result.content,
      };
    },

    download(filename) {
      window.open(`/api/geo/files/${filename}?download=1`, '_blank');
    },

    formatSize(bytes) {
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
      return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    },
  },
};
</script>
```

## 最佳实践

### 1. 内容规范

**标题规范**:
- 长度：3-4 个词（中文）或 5-8 个单词（英文）
- 格式：`主标题 - 副标题 - 站点名`
- 示例：`快速开始 - VitePress 文档`

**描述规范**:
- 长度：9-10 个词（中文）或 15-20 个单词（英文）
- 内容：包含关键词和核心价值
- 示例：`5分钟快速上手 VitePress 静态站点生成器`

**URL 规范**:
- 使用小写字母和连字符
- 避免特殊字符
- 示例：`/getting-started` 而不是 `/Getting_Started`

### 2. 性能优化

- **缓存策略**: 生成文件后缓存 1-24 小时
- **按需生成**: 只在内容更新时重新生成
- **队列处理**: 大量内容时使用队列异步生成
- **CDN 加速**: 将生成的文件部署到 CDN

### 3. 监控和日志

```php
// 记录生成日志
Log::info('GEO files generated', [
    'site_id' => $siteId,
    'files' => $generated,
    'duration' => $duration,
    'memory' => memory_get_peak_usage(true),
]);

// 监控错误
if (!empty($errors)) {
    Log::error('GEO generation errors', [
        'site_id' => $siteId,
        'errors' => $errors,
    ]);
}
```

### 4. 安全考虑

- **API 认证**: 所有 GEO 生成接口需要认证
- **权限控制**: 只允许站点管理员生成文件
- **敏感数据**: 不要在 GEO 文件中包含敏感信息
- **访问限制**: 可以通过 robots.txt 控制搜索引擎访问

## 故障排除

### 问题 1: 生成的内容不完整

**原因**: 数据库中缺少必要字段

**解决方案**:
```php
// 验证数据完整性
$contents->each(function ($content) {
    if (empty($content->title)) {
        Log::warning('Content missing title', ['id' => $content->id]);
    }
    if (empty($content->description)) {
        $content->description = $this->generateDescription($content);
    }
});
```

### 问题 2: 生成速度慢

**原因**: 内容过多或 I/O 瓶颈

**解决方案**:
- 使用队列异步生成
- 分批处理内容
- 启用缓存

### 问题 3: AI 引擎不收录

**原因**: GEO 文件格式不规范

**解决方案**:
- 验证生成的文件是否符合规范
- 检查 robots.txt 是否允许爬取
- 在 llms.txt 中添加完整的元数据

## 总结

通用后台管理方案的核心是：

1. **结构化存储**: 在数据库中维护符合 GEO 规范的内容
2. **灵活生成**: 使用 Geo-Friendly 包按需生成标准文件
3. **多种部署**: 支持代理、同步、混合等多种部署模式
4. **完整集成**: 从编辑到生成的完整工作流

这个方案既保持了灵活性，又遵循了 GEO 的标准规范，适合各种类型的项目。
