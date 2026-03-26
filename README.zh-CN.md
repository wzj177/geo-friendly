# Geo-Friendly

[![Latest Version](https://img.shields.io/packagist/v/wzj177/geo-friendly)](https://packagist.org/packages/wzj177/geo-friendly)
[![PHP Version](https://img.shields.io/php/v/wzj177/geo-friendly)](https://packagist.org/packages/wzj177/geo-friendly)

PHP 生成式引擎优化（GEO）- 让您的网站可被 ChatGPT、Claude 和 Perplexity 等 AI 答案引擎发现。

[English](README.md) | 简体中文

## 什么是 GEO？

GEO（生成式引擎优化）针对 AI 驱动的答案引擎优化内容。此包生成 AI 引擎用于理解和索引您的网站的标准文件。

## 特性

- **AI 友好文件**：`llms.txt`、`llms-full.txt`、`robots.txt`、`sitemap.xml`、`docs.json`、`ai-index.json`、`schema.json`
- **CLI 工具**：简单的命令行界面
- **三种模式**：本地 Markdown 文件、Firecrawl API、内容数组（数据库）
- **平台就绪**：WordPress、Shopify、Laravel、Symfony 集成

## 系统要求

- PHP 7.4+
- Composer
- 扩展：`json`、`simplexml`、`yaml`

## 安装

```bash
composer require wzj177/geo-friendly
```

## 快速开始

### 方式 1：CLI 工具（推荐用于静态站点）

```bash
# 创建配置文件
vendor/bin/geo init

# 编辑 geofriendly.yaml，然后生成
vendor/bin/geo generate
```

**geofriendly.yaml**：
```yaml
title: '我的网站'
url: 'https://example.com'
contentDir: './content'  # 本地 markdown 文件
outDir: './public'
```

### 方式 2：内容数组（用于数据库内容）

```php
use GeoFriendly\GeoFriendly;

$contents = [
    [
        'title' => '快速开始',
        'url' => '/getting-started',
        'content' => '# 快速开始\n\n这是内容...',
        'description' => '学习如何开始',
        'category' => '指南',
    ],
    [
        'title' => 'API 参考',
        'url' => '/api/reference',
        'content' => '# API 参考\n\n...',
        'description' => '完整的 API 文档',
        'category' => 'API',
    ],
];

$config = [
    'title' => '我的文档',
    'url' => 'https://docs.example.com',
    'outDir' => __DIR__ . '/public',
    'contents' => $contents,  // 直接传入内容数组
];

$geo = new GeoFriendly($config);
[$generated, $errors] = $geo->generate();
```

### 方式 3：Firecrawl（用于任何网站）

```yaml
title: '我的商店'
url: 'https://store.example.com'
contentDir: ''  # 空值 = 使用 Firecrawl
firecrawl:
  apiKey: 'your-api-key'
  enabled: true
```

## 生成的文件

| 文件 | 用途 |
|------|------|
| `llms.txt` | LLM 发现（遵循 [llms-txt.org](https://llms-txt.org)） |
| `llms-full.txt` | AI 训练的完整文档 |
| `robots.txt` | AI 爬虫权限 |
| `sitemap.xml` | SEO 站点地图 |
| `docs.json` | 结构化文档索引 |
| `ai-index.json` | AI 优化的内容索引 |
| `schema.json` | Schema.org 结构化数据 |

## CLI 命令

```bash
vendor/bin/geo generate          # 生成所有文件
vendor/bin/geo init             # 创建 geofriendly.yaml
vendor/bin/geo check            # 审计 GEO 状态
vendor/bin/geo report           # 生成详细报告
```

## 内容数组格式

从数据库传入内容时：

```php
$contents = [
    [
        'title' => string,        // 必需：页面标题
        'url' => string,          // 必需：页面 URL (如 /getting-started)
        'content' => string,      // 必需：Markdown 内容
        'description' => string,  // 可选：AI 友好描述 (9-10个词)
        'category' => string,     // 可选：内容分类
        'tags' => array,          // 可选：内容标签
    ],
    // ... 更多项目
];
```

## 使用场景

### 1. 静态站点 + Markdown（VitePress、Docusaurus、Hugo）

```yaml
title: '我的文档'
url: 'https://docs.example.com'
contentDir: './content'
firecrawl:
  enabled: false
```

**示例**：[examples/vitepress-site](examples/vitepress-site)

### 2. 动态站点（WordPress、Shopify）

```yaml
title: '我的 WordPress 站点'
url: 'https://mysite.com'
contentDir: ''
firecrawl:
  apiKey: '%env(FIRECRAWL_API_KEY)%'
  enabled: true
```

**示例**：[examples/wordpress-firecrawl](examples/wordpress-firecrawl)

### 3. 自定义后台（SaaS、企业）

```php
// 从数据库获取
$contents = Content::where('status', 'published')
    ->get()
    ->map(fn($c) => [
        'title' => $c->title,
        'url' => $c->slug,
        'content' => $c->markdown_content,
        'description' => $c->description,
    ])
    ->toArray();

$config = [
    'title' => '我的平台',
    'url' => 'https://app.example.com',
    'outDir' => storage_path('geo'),
    'contents' => $contents,
];

$geo = new GeoFriendly($config);
[$generated, $errors] = $geo->generate();
```

**完整指南**：[docs/generic-backend-solution.md](docs/generic-backend-solution.md)

## 文档

- [内容模式](docs/content-modes.zh-CN.md) - 本地文件 vs Firecrawl
- [AI 集成](docs/AI-INTEGRATION.zh-CN.md) - OpenAI 增强
- [后台方案](docs/generic-backend-solution.md) - 数据库集成

## 许可证

MIT
