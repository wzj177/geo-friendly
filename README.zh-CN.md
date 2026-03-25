# Geo-Friendly

[![Latest Stable Version](https://img.shields.io/packagist/v/geo-friendly/geo-friendly)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![Total Downloads](https://img.shields.io/packagist/dt/geo-friendly/geo-friendly)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![License](https://img.shields.io/packagist/l/geo-friendly/geo-friendly)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![PHP Version](https://img.shields.io/php/v/geo-friendly/geo-friendly)](https://packagist.org/packages/geo-friendly/geo-friendly)

PHP 生成式引擎优化（GEO）- 让您的网站可被 ChatGPT、Claude 和 Perplexity 等 AI 答案引擎发现。

English | [简体中文](README.zh-CN.md)

## 什么是 GEO？

生成式引擎优化（GEO）是 SEO 的下一个演进阶段，专注于让您的内容可被 AI 驱动的答案引擎发现并以最佳格式呈现。此包帮助您生成 AI 引擎用于理解和索引您的网站所需的所有文件。

## 特性

- **AI 友好文件生成**：自动生成 AI 引擎偏好的文件
  - `llms.txt` - LLM 的结构化内容（遵循 [llms-txt.org](https://llms-txt.org)）
  - `llms-full.txt` - AI 训练的综合文档
  - `robots.txt` - 增强的 AI 爬虫指令
  - `sitemap.xml` - SEO 优化的站点地图
  - `docs.json` - 结构化文档索引
  - `ai-index.json` - AI 友好的内容索引
  - `schema.json` - Schema.org 标记用于富搜索结果

- **CLI 工具**：易用的命令行界面，包含多个命令
- **灵活配置**：基于 YAML 的配置，完全可定制
- **框架无关**：适用于任何 PHP 项目或框架
- **平台集成**：现成的 WordPress、Shopify、Laravel 和 Symfony 集成
- **审计和报告**：内置 GEO 分数计算器和详细报告

## 系统要求

- PHP 7.4 或更高版本
- Composer
- 扩展：`json`、`simplexml`、`yaml`

## 安装

通过 Composer 安装：

```bash
composer require geo-friendly/geo-friendly
```

或添加到您的 `composer.json`：

```json
{
    "require": {
        "geo-friendly/geo-friendly": "^1.0"
    }
}
```

安装后，您可以直接使用 CLI 工具：

```bash
# geo 命令现已可用
vendor/bin/geo --version
```

## 快速开始

### 使用 CLI 工具

安装后，`geo` 命令将在您的 vendor bin 中可用：

```bash
# 在项目根目录生成所有 GEO 文件
vendor/bin/geo generate

# 生成特定文件
vendor/bin/geo generate:llms
vendor/bin/geo generate:sitemap
vendor/bin/geo generate:schema

# 使用自定义输出目录生成
vendor/bin/geo generate --output=./public

# 使用自定义配置生成
vendor/bin/geo generate --config=./geo-config.yaml
```

### 编程方式使用

```php
use GeoFriendly\GeoGenerator;

$generator = new GeoGenerator([
    'site_url' => 'https://example.com',
    'site_name' => '我的精彩网站',
    'output_dir' => __DIR__ . '/public',
]);

// 生成所有文件
$generator->generateAll();

// 或生成特定文件
$generator->generateLlmsTxt();
$generator->generateSitemap();
$generator->generateSchema();
```

## 配置

### 内容模式

Geo-Friendly 支持两种内容生成模式：

1. **本地文件模式**（默认）- 从 `contentDir` 使用 markdown 文件
2. **Firecrawl 模式**- 使用 Firecrawl API 爬取网站

#### 本地文件模式

最适合文档站点、博客，以及当您可以访问源 markdown 文件时：

```yaml
title: '我的文档'
url: 'https://docs.example.com'
contentDir: './content'
```

#### Firecrawl 模式

最适合电商网站、企业网站，或需要爬取外部网站时：

```yaml
title: '我的商店'
url: 'https://store.example.com'
contentDir: ''  # 空值以使用 Firecrawl
firecrawl:
  apiKey: 'your-firecrawl-api-key'
  apiUrl: 'https://api.firecrawl.dev/v1'
  enabled: true
```

**何时使用每种模式：**
- **本地文件**：文档站点（Docusaurus、MkDocs）、博客（Hugo、Jekyll）、知识库
- **Firecrawl**：电商网站、企业网站、SaaS 应用、动态内容

详细信息请参阅[内容模式文档](docs/content-modes.md)。

### 基本配置

在项目根目录创建 `geofriendly.yaml` 文件：

```yaml
# 基本站点信息
site_url: https://example.com
site_name: 我的精彩网站
site_description: 您网站的描述

# 输出目录（相对于项目根目录）
output_dir: ./public

# 要生成的文件
generate:
  llms_txt: true
  llms_full: true
  robots_txt: true
  sitemap_xml: true
  docs_json: true
  ai_index: true
  schema_json: true

# 内容源
content_sources:
  - ./docs/**/*.md
  - ./src/**/*.php

# 排除模式
exclude:
  - vendor/
  - node_modules/
  - tests/

# AI 爬虫权限（用于 robots.txt）
ai_crawlers:
  GPTBot: allow
  Google-Extended: allow
  anthropic-ai: allow
  PerplexityBot: allow
```

## CLI 命令

`geo` CLI 工具提供管理 GEO 文件的全面功能：

```bash
# 生成所有 GEO 文件
vendor/bin/geo generate

# 生成特定文件类型
vendor/bin/geo generate:llms          # 生成 llms.txt 和 llms-full.txt
vendor/bin/geo generate:robots        # 生成 robots.txt
vendor/bin/geo generate:sitemap       # 生成 sitemap.xml
vendor/bin/geo generate:schema        # 生成 schema.json
vendor/bin/geo generate:docs          # 生成 docs.json
vendor/bin/geo generate:ai-index      # 生成 ai-index.json

# 初始化配置
vendor/bin/geo init                   # 在当前目录创建 geo-config.yaml

# 验证配置
vendor/bin/geo validate               # 验证 geo-config.yaml

# 检查当前 GEO 状态
vendor/bin/geo check                  # 分析当前站点并提供建议

# 生成详细报告
vendor/bin/geo report                 # 生成包含分数的综合 GEO 报告

# 显示帮助
vendor/bin/geo --help
vendor/bin/geo generate --help        # 特定命令的帮助

# 显示版本
vendor/bin/geo --version
```

### 命令选项

所有生成命令都支持这些选项：

```bash
# 自定义输出目录
vendor/bin/geo generate --output=./public

# 自定义配置文件
vendor/bin/geo generate --config=./custom-config.yaml

# 详细输出
vendor/bin/geo generate --verbose

# 试运行（预览更改而不写入）
vendor/bin/geo generate --dry-run
```

## 生成的文件

### llms.txt

遵循 [llms-txt.org](https://llms-txt.org) 规范的结构化文件，为 AI 引擎提供您内容结构的信息。

```txt
# 我的精彩网站
Title: 我的精彩网站
Description: 您网站的描述
Version: 1.0.0

## 文档
- [入门指南](https://example.com/docs/getting-started.md)
- [API 参考](https://example.com/docs/api.md)

## 博客
- [最新文章](https://example.com/blog/feed.xml)
```

### llms-full.txt

包含完整文档内容的综合版本，用于 AI 训练数据。包括完整的文章内容、代码示例和针对 LLM 消费优化的详细描述。

### robots.txt

增强的 robots.txt，包含 AI 爬虫指令：

```txt
User-agent: *
Allow: /

# AI 爬虫
User-agent: GPTBot
Allow: /

User-agent: Google-Extended
Allow: /

User-agent: anthropic-ai
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: Claude-Web
Allow: /

# 站点地图
Sitemap: https://example.com/sitemap.xml
```

### sitemap.xml

SEO 优化的 XML 站点地图，具有适当的优先级和更改频率。包含所有页面、文章和自定义文章类型及其元数据。

### docs.json

JSON 格式的结构化文档索引，提供文档结构的机器可读概览，具有层次化组织和元数据。

### ai-index.json

AI 友好的内容索引，将您的内容映射到最佳的 AI 发现格式，包括摘要、关键词和相关性分数。

### schema.json

用于富搜索结果的 Schema.org 结构化数据，包括 WebSite、WebPage、Article 和 Organization 模式及其完整元数据。

## 高级用法

### 自定义内容处理器

您可以使用自定义内容处理器扩展生成器：

```php
use GeoFriendly\Processor\ContentProcessorInterface;

class CustomProcessor implements ContentProcessorInterface
{
    public function process(string $content, array $metadata): array
    {
        // 您的自定义处理逻辑
        return [
            'title' => $metadata['title'] ?? '',
            'content' => strip_tags($content),
            'summary' => substr($content, 0, 500),
        ];
    }
}

// 注册处理器
$generator->addProcessor(new CustomProcessor());
```

### 框架集成

**Laravel：**

```php
// 在命令或控制器中
use GeoFriendly\GeoGenerator;

class GenerateGeoCommand extends Command
{
    public function handle()
    {
        $generator = new GeoGenerator([
            'site_url' => config('app.url'),
            'site_name' => config('app.name'),
            'output_dir' => public_path(),
        ]);

        $generator->generateAll();

        $this->info('GEO 文件生成成功！');
    }
}
```

完整的 Laravel 集成包也可用：

```bash
# 安装 Laravel 服务提供者
composer require geo-friendly/geo-friendly
php artisan vendor:publish --provider="GeoFriendly\Laravel\GeoFriendlyServiceProvider"
```

参见 [examples/laravel](examples/laravel) 获取完整的 Laravel 集成，包括：
- 服务提供者
- Artisan 命令
- 配置发布
- 用于自动重新生成的中间件

**Symfony：**

```php
// 在控制台命令中
use GeoFriendly\GeoGenerator;

class GenerateGeoCommand extends Command
{
    protected static $defaultName = 'geo:generate';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $generator = new GeoGenerator([
            'site_url' => $this->getParameter('router.request_context.host'),
            'site_name' => $this->getParameter('app.name'),
            'output_dir' => $this->getParameter('kernel.project_dir') . '/public',
        ]);

        $generator->generateAll();

        return Command::SUCCESS;
    }
}
```

完整的 Symfony 包可在 [examples/symfony](examples/symfony) 获取，包含：
- 包配置
- 控制台命令
- Twig 集成
- 事件订阅者

**WordPress：**

WordPress 插件可在 [examples/wordpress-plugin](examples/wordpress-plugin) 获取，功能包括：
- 自动 GEO 文件生成
- 管理界面用于配置
- 自定义设置页面
- 与 WordPress cron 集成

**Shopify：**

Shopify 应用模板可在 [examples/shopify-app](examples/shopify-app) 获取，包含：
- 主题应用扩展
- 自动文件生成
- 管理界面
- 多语言支持

## AI 增强

该包包括用于内容优化的 AI 驱动功能：

### AI 驱动的内容增强

```php
use GeoFriendly\GeoGenerator;
use GeoFriendly\Enhancement\AiContentEnhancer;

$generator = new GeoGenerator($config);
$enhancer = new AiContentEnhancer($openaiApiKey);

// 使用 AI 增强内容
$enhancedContent = $enhancer->enhanceForLlm($originalContent, [
    'add_context' => true,
    'summarize' => true,
    'extract_keywords' => true,
]);

// 生成 AI 优化的 llms.txt
$generator->setEnhancer($enhancer);
$generator->generateLlmsTxt();
```

### 功能

- **内容摘要**：自动创建 AI 友好的摘要
- **关键词提取**：提取相关关键词以便 AI 发现
- **上下文增强**：添加上下文信息以改善 AI 理解
- **模式生成**：为 AI 引擎生成优化的结构化数据

## GEO 审计和报告

该包包括审计您网站 GEO 准备情况的工具：

```bash
# 检查您网站的 GEO 状态
vendor/bin/geo check --url=https://example.com

# 生成详细报告
vendor/bin/geo report --url=https://example.com --output=geo-report.html
```

### GEO 分数

审计根据以下因素计算综合 GEO 分数（0-100）：

- **llms.txt 存在**（20 分）
- **Robots.txt AI 爬虫规则**（15 分）
- **Schema.org 标记**（15 分）
- **站点地图完整性**（10 分）
- **内容结构**（20 分）
- **元数据质量**（10 分）
- **AI 友好格式**（10 分）

## 测试

运行测试套件：

```bash
composer test
```

运行特定测试套件：

```bash
# 仅单元测试
vendor/bin/phpunit --testsuite=Unit

# 集成测试
vendor/bin/phpunit --testsuite=Integration

# 功能测试
vendor/bin/phpunit --testsuite=Feature
```

运行静态分析：

```bash
composer analyse
```

## 贡献

欢迎贡献！请随时提交 Pull Request。

### 开发设置

```bash
# 克隆仓库
git clone https://github.com/geo-friendly/geo-friendly.git
cd geo-friendly

# 安装依赖
composer install

# 运行测试
composer test

# 运行分析
composer analyse
```

## 许可证

此包是根据 [MIT 许可证](LICENSE.md) 许可的开源软件。

## 致谢

- [Geo-Friendly 贡献者](https://github.com/geo-friendly/geo-friendly/graphs/contributors)
- 从 GEO 社区获得灵感构建

## 支持

- **文档**：[完整文档](https://github.com/geo-friendly/geo-friendly/docs)
- **问题**：[GitHub Issues](https://github.com/geo-friendly/geo-friendly/issues)
- **讨论**：[GitHub Discussions](https://github.com/geo-friendly/geo-friendly/discussions)
- **平台示例**：[examples/](examples/) 目录

## 相关资源

- [llms-txt.org](https://llms-txt.org) - LLMs.txt 文件规范
- [Schema.org](https://schema.org) - 结构化数据标记
- [Google 的 AI 爬虫](https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers)
- [OpenAI 关于内容可发现性的文档](https://platform.openai.com/docs)
- [Anthropic 的 Claude 文档](https://docs.anthropic.com)
- [Perplexity AI 文档](https://docs.perplexity.ai)

## 更新日志

请参阅 [CHANGELOG.md](CHANGELOG.md) 了解最近的更改。

---

用 ❤️ 为开源社区构建
