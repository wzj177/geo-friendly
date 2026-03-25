# Geo-Friendly 功能特性

Geo-Friendly 是一个 PHP 7.4+ Composer 包，用于生成式引擎优化（GEO），帮助网站被 ChatGPT、Claude、Perplexity 等 AI 答案引擎发现和引用。

## 核心功能

### 1. AI 友好文件生成

自动生成针对 AI 答案引擎优化的文件：

- **robots.txt** - 包含 15+ AI 爬虫指令（GPTBot、Claude-Web、CCBot 等）
- **llms.txt** - AI 发现文件，帮助 LLM 理解你的站点结构
- **llms-full.txt** - 完整的 LLMs 文档索引
- **sitemap.xml** - 标准 XML 站点地图
- **docs.json** - 结构化的文档索引
- **ai-index.json** - 增强的 AI 索引文件
- **schema.json** - Schema.org JSON-LD 结构化数据

### 2. 双内容模式

#### 本地文件模式（默认）
- 从本地 markdown 文件生成
- 适用于静态站点生成器、文档站点、博客
- 快速、免费、离线可用

#### Firecrawl 模式（增强）
- 通过 Firecrawl API 爬取网站
- 自动将 HTML 转换为 markdown
- 适用于动态网站（WordPress、Shopify 等）
- 无需访问源文件

### 3. AI 内容增强（可选）

集成 OpenAI API 进行内容优化：

- **更好的描述** - AI 生成优化的标题和描述
- **SEO 优化** - 关键词提取和内容改进
- **Schema 增强** - AI 生成的结构化数据描述
- **自动选择** - 配置 OpenAI 时自动使用 AI 增强生成器

### 4. CLI 工具

提供功能完整的命令行界面：

```bash
# 生成 GEO 文件
./bin/geo generate

# 初始化配置
./bin/geo init

# 检查 GEO 就绪状态
./bin/geo check

# 生成详细报告
./bin/geo report
```

### 5. 平台集成

#### WordPress
```php
// WordPress 插件示例
use GeoFriendly\Integrations\WordPress\GeoFriendlyWP;

GeoFriendlyWP::init();
```

#### Shopify
```php
// Shopify Admin API 集成
use GeoFriendly\Integrations\Shopify\ShopifyClient;

$client = new ShopifyClient($storeDomain, $accessToken);
$client->generateAll();
```

#### Laravel
```php
// Laravel 服务提供者
php artisan vendor:publish --provider="GeoFriendly\Integrations\Laravel\GeoFriendlyServiceProvider"

// 生成 GEO 文件
php artisan geo:generate
```

#### Symfony
```php
// Symfony Bundle
geo_friendly:
  title: '%app.name%'
  url: '%app.url%'
  content_dir: '%kernel.project_dir%/content'
```

### 6. 配置系统

使用 YAML 配置文件支持环境变量：

```yaml
title: '我的站点'
url: 'https://example.com'
contentDir: './content'
outDir: './public'

# Firecrawl 配置
firecrawl:
  apiKey: '%env(FIRECRAWL_API_KEY)%'
  enabled: true

# OpenAI 配置
openai:
  apiKey: '%env(OPENAI_API_KEY)%'
  baseUrl: 'https://api.openai.com/v1'
  model: 'gpt-4o-mini'
  enabled: true
```

### 7. GEO 就绪审计

内置审计系统评估你的 GEO 准备情况：

- **文件存在审计** - 检查所有 GEO 文件是否存在
- **格式验证审计** - 验证文件格式和语法
- **内容质量审计** - 评估内容质量和完整性
- **社交媒体审计** - 检查 Open Graph 和 Twitter Card

生成详细的评分报告和改进建议。

### 8. 测试和 CI/CD

完整的测试基础设施：

- PHPUnit 测试套件
- GitHub Actions 工作流
- 自动化 CI/CD 管道
- 代码质量检查

### 9. 文档

全面的文档支持：

- 中英文 README（README.md / README.zh-CN.md）
- AI 集成指南（AI-INTEGRATION.md）
- 内容模式说明（content-modes.md）
- 平台集成示例
- API 参考文档

## 技术栈

- **PHP** - 7.4+（兼容广泛，支持传统和现代环境）
- **Composer** - 包管理和 PSR-4 自动加载
- **Symfony Console** - CLI 框架
- **Symfony YAML** - 配置解析
- **Guzzle HTTP** - HTTP 客户端（Firecrawl API）
- **League CommonMark** - Markdown 解析
- **OpenAI PHP Client** - OpenAI API 集成（可选）

## 使用场景

### 文档站点
```bash
# Docusaurus、MkDocs、VuePress 等文档站点
./bin/geo generate --config=geofriendly.yaml
```

### 博客
```yaml
# Hugo、Hexo、Jekyll 等博客
contentDir: './content'
generators:
  llmsTxt: true
  sitemap: true
```

### 动态网站
```yaml
# WordPress、Shopify 等 CMS
firecrawl:
  apiKey: '%env(FIRECRAWL_API_KEY)%'
  enabled: true
```

### 框架集成
```php
// Laravel、Symfony、WordPress、Shopify
use GeoFriendly\GeoFriendly;

$geo = new GeoFriendly($config);
[$generated, $errors] = $geo->generate();
```

## 优势

1. **多语言支持** - 中英文文档和配置示例
2. **灵活配置** - YAML 配置支持环境变量
3. **平台兼容** - 支持主流 PHP 框架和 CMS
4. **AI 增强** - 可选的 OpenAI 集成提升内容质量
5. **双模式** - 本地文件和 Firecrawl 爬取两种模式
6. **完整 CLI** - 功能齐全的命令行工具
7. **审计系统** - 评估和改进 GEO 就绪状态
8. **广泛兼容** - 支持 PHP 7.4+，适用于各种环境
