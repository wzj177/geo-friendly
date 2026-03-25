# 内容模式

Geo-Friendly 支持两种内容生成模式：

1. **本地文件模式**（默认）- 使用来自 `contentDir` 的 markdown 文件
2. **Firecrawl 模式** - 使用 Firecrawl API 爬取网站

## 本地文件模式

这是默认的推荐模式，适用于大多数用例。Geo-Friendly 扫描你的本地 markdown 文件并生成 AI 友好的索引。

### 何时使用

- 你拥有 markdown 格式的现有文档
- 你正在构建文档站点、博客或知识库
- 你希望完全控制内容结构
- 你需要离线能力

### 设置

1. 在项目根目录中创建 `content` 目录
2. 添加你的 markdown 文件：
   ```
   content/
   ├── index.md
   ├── getting-started.md
   └── api/
       ├── authentication.md
       └── endpoints.md
   ```

3. 在 `geofriendly.yaml` 中配置：
   ```yaml
   contentDir: './content'
   ```

### 示例

**文档站点**
- Docusaurus
- MkDocs
- VuePress
- Jekyll 博客

**博客**
- Hugo
- Hexo
- Jekyll
- 任何具有 markdown 内容的静态站点生成器

**知识库**
- 公司 wiki
- API 文档
- 用户指南

## Firecrawl 模式

Firecrawl 模式允许 Geo-Friendly 从实时网站爬取和提取内容。当你无法访问源文件或需要索引动态内容时，这很有用。

### 何时使用

- 你需要索引你不拥有的网站
- 站点使用动态内容生成
- 你想在没有文件访问的情况下快速原型化
- 站点使用没有简单导出功能的 CMS

### 设置

1. 从 [firecrawl.dev](https://www.firecrawl.dev/) 获取 Firecrawl API 密钥
2. 在 `geofriendly.yaml` 中配置：
   ```yaml
   firecrawl:
     apiKey: 'your-api-key-here'
     apiUrl: 'https://api.firecrawl.dev/v1'
     enabled: true
   ```

3. 将 `contentDir` 留空或设置为 `''`：
   ```yaml
   contentDir: ''
   ```

### 工作原理

当 `contentDir` 为空且启用 Firecrawl 时：

1. Geo-Friendly 使用你的站点 URL 调用 Firecrawl API
2. Firecrawl 爬取并以 markdown 格式提取内容
3. 提取的内容用于生成 AI 友好的文件

### 示例

**电商站点**
- 产品目录站点
- 在线商店（Shopify、WooCommerce 等）

**企业网站**
- 公司信息页面
- 服务描述
- 关于页面

**新闻/媒体站点**
- 文章集合
- 新闻档案
- 博客文章

**SaaS 应用程序**
- 功能文档
- 定价页面
- 帮助中心

## 选择正确的模式

### 使用本地文件模式：

- 你可以访问源 markdown 文件
- 内容相对静态
- 你需要精确控制索引的内容
- 你正在构建文档或知识库

### 使用 Firecrawl 模式：

- 你需要索引外部网站
- 内容是动态生成的
- 你没有源文件访问权限
- 你正在进行原型设计或审计

### 混合方法

你可以同时使用两种模式：

```yaml
contentDir: './content'  # 本地文件
firecrawl:
  apiKey: 'your-key'
  enabled: true
```

在此配置中：
- 本地文件首先被索引
- Firecrawl 用其他内容补充
- 你可以获得两全其美的效果

## 配置示例

### 静态文档站点（本地文件）

```yaml
title: '我的文档'
url: 'https://docs.example.com'
contentDir: './docs'
generators:
  llmsTxt: true
  sitemap: true
```

### 电商站点（Firecrawl）

```yaml
title: '我的商店'
url: 'https://store.example.com'
contentDir: ''  # 空值以使用 Firecrawl
firecrawl:
  apiKey: 'fc-...'
  enabled: true
generators:
  llmsTxt: true
  sitemap: true
```

### 混合配置

```yaml
title: '我的平台'
url: 'https://platform.example.com'
contentDir: './content'  # 本地文档
firecrawl:
  apiKey: 'fc-...'
  enabled: true  # 同时爬取网页内容
generators:
  llmsTxt: true
  llmsFullTxt: true
```

## 最佳实践

### 本地文件模式

1. **逻辑组织内容**
   - 使用清晰的目录结构
   - 描述性命名文件
   - 使用一致的格式

2. **添加 frontmatter**
   ```yaml
   ---
   title: "页面标题"
   description: "页面描述"
   tags: [api, integration]
   ---
   ```

3. **保持内容更新**
   - 定期审查和更新
   - 删除过时文件
   - 维护一致性

### Firecrawl 模式

1. **遵守速率限制**
   - 不要过于频繁地重新爬取
   - 使用适当的超时设置
   - 监控 API 使用情况

2. **优雅处理错误**
   - 检查提取失败
   - 记录问题以供审查
   - 有备用内容

3. **优化爬取目标**
   - 尽可能爬取特定部分
   - 避免不必要的页面
   - 专注于高价值内容

## 故障排除

### 未找到本地文件

- 验证 `contentDir` 路径是否正确
- 检查文件权限
- 确保文件具有 `.md` 或 `.mdx` 扩展名

### Firecrawl 失败

- 验证 API 密钥是否有效
- 检查 API 速率限制
- 确保目标 URL 可访问
- 查看错误日志了解详细信息

### 混合内容问题

- 在爬取之间清除缓存
- 检查重复内容
- 验证 URL 配置

## 高级配置

### 自定义 Firecrawl 端点

```yaml
firecrawl:
  apiKey: 'your-key'
  apiUrl: 'https://custom-proxy.example.com/v1'
  enabled: true
```

### 条件模式选择

```php
use GeoFriendly\Utils\FileHelper;

// 在你的代码中
$config = GeofriendlyConfig::fromArray($configArray);

if (!empty($config->contentDir)) {
    // 使用本地文件
    $files = FileHelper::collectMarkdownFiles($config->contentDir);
} elseif ($config->firecrawl['enabled']) {
    // 使用 Firecrawl
    $content = FileHelper::extractContent($config->url, $config);
}
```

## 性能考虑

### 本地文件模式

- 快速 - 无网络请求
- 可扩展到数千个文件
- 低资源使用

### Firecrawl 模式

- 取决于 API 响应时间
- 受速率限制
- 依赖网络
- 可能产生 API 成本

## 安全考虑

### 本地文件模式

- 确保正确的文件权限
- 验证文件路径
- 扫描恶意内容

### Firecrawl 模式

- 保护 API 密钥
- 使用环境变量
- 监控未经授权的访问
- 遵守 robots.txt
