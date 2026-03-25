# OpenAI/Firecrawl 集成指南

本文档介绍 geo-friendly 中 OpenAI 和 Firecrawl 集成的工作原理。

## 内容模式

### 模式 1：本地 Markdown 文件（默认）

**工作原理：**
- 从 `contentDir` 目录读取 `.md` 和 `.mdx` 文件
- 使用 FileHelper 递归收集 markdown 文件
- 解析 frontmatter 以获取标题和描述
- 无需外部 API 调用

**适用场景：**
- ✅ 静态网站生成器（Jekyll、Hugo、Astro、Next.js with SSG）
- ✅ 包含 markdown 内容的文档站点
- ✅ GitBook、MkDocs、Docusaurus 站点
- ✅ 包含 markdown 文章的博客
- ✅ 任何你可以控制内容源头的站点

**维护要求：**
1. 将 markdown 文件保存在 `content/` 目录中
2. 使用 frontmatter 设置标题/描述：
   ```yaml
   ---
   title: "页面标题"
   description: "LLM 的页面描述"
   ---
   ```
3. 遵循命名规范：`page-name.md` 或 `page-name.mdx`

**目录结构：**
```
project/
├── content/
│   ├── getting-started.md
│   ├── api/
│   │   └── reference.md
│   └── guides/
│       └── tutorial.md
├── public/           # 生成的 GEO 文件放在这里
└── geofriendly.yaml
```

### 模式 2：Firecrawl API（增强模式）

**工作原理：**
- 使用 Firecrawl API 爬取和提取网站内容
- 自动将 HTML 转换为 markdown
- 需要设置 `FIRECRAWL_API_KEY` 环境变量
- 如果 API 失败，则回退到本地文件

**适用场景：**
- ✅ 动态网站（WordPress、Shopify、自定义 PHP 应用）
- ✅ 没有 markdown 源文件的站点
- ✅ 你不想修改的遗留站点
- ✅ 无需内容迁移的快速测试
- ✅ 内容结构复杂的站点

**设置：**
```yaml
# geofriendly.yaml
firecrawl:
  apiKey: '%env(FIRECRAWL_API_KEY)%'
  apiUrl: 'https://api.firecrawl.dev/v1'
  enabled: true
```

**环境变量：**
```bash
export FIRECRAWL_API_KEY=fc-...
```

## OpenAI 增强

### AI 功能（可选）

当配置 `openai.apiKey` 时，geo-friendly 使用 AI 来：

1. **生成更好的描述**
   - AI 分析内容并创建优化的描述
   - 3-4 个词的标题和 9-10 个词的描述
   - 比通用描述更好

2. **内容增强**
   - AI 发现的 SEO 优化
   - 关键词提取
   - 可读性改进

3. **Schema.org 增强**
   - AI 为结构化数据生成描述
   - 更好的搜索引擎理解

**设置：**
```yaml
# geofriendly.yaml
openai:
  apiKey: '%env(OPENAI_API_KEY)%'
  baseUrl: 'https://api.openai.com/v1'  # 或自定义端点
  model: 'gpt-4o-mini'
```

**要求：**
```bash
composer require openai-php/client
```

## 使用示例

### 示例 1：静态站点（本地文件）

```bash
# 站点结构
my-docs/
├── content/
│   ├── guide.md
│   └── api.md
├── public/
└── geofriendly.yaml

# 生成 GEO 文件
./bin/geo generate
```

**配置：**
```yaml
title: '我的文档'
url: 'https://docs.example.com'
contentDir: './content'
outDir: './public'

# 不需要 Firecrawl - 使用本地 markdown
```

### 示例 2：WordPress 站点（Firecrawl）

```bash
# 设置 API 密钥
export FIRECRAWL_API_KEY=fc-...

# 通过爬取生成 GEO 文件
./bin/geo generate --url=https://mysite.com --title="我的站点"
```

**配置：**
```yaml
title: '我的站点'
url: 'https://mysite.com'
outDir: './public'

# 启用 Firecrawl 进行内容提取
firecrawl:
  apiKey: '%env(FIRECRAWL_API_KEY)%'
  enabled: true
```

### 示例 3：增强模式（OpenAI + Firecrawl）

```bash
# 设置两个 API 密钥
export FIRECRAWL_API_KEY=fc-...
export OPENAI_API_KEY=sk-...

# 使用 AI 增强生成
./bin/geo generate
```

**配置：**
```yaml
title: '我的站点'
url: 'https://mysite.com'
outDir: './public'

# Firecrawl 用于内容提取
firecrawl:
  apiKey: '%env(FIRECRAWL_API_KEY)%'
  enabled: true

# OpenAI 用于增强
openai:
  apiKey: '%env(OPENAI_API_KEY)%'
  model: 'gpt-4o-mini'
```

## 成本考虑

### 本地文件模式
- **成本：** 免费
- **性能：** 快速（本地文件读取）
- **维护：** 需要维护 markdown 文件

### Firecrawl 模式
- **成本：** 取决于 Firecrawl 定价
- **性能：** 较慢（API 调用）
- **维护：** 无需内容更改

### OpenAI 增强
- **成本：** 取决于 OpenAI API 使用情况
- **性能：** 较慢（API 调用）
- **质量：** 更好的 AI 优化内容

## 建议

| 网站类型 | 推荐模式 | 原因 |
|--------------|------------------|---------|
| 文档站点 | 本地文件 | 你可以控制 markdown，更快 |
| 包含 markdown 的博客 | 本地文件 | 原生 markdown 支持 |
| WordPress 站点 | Firecrawl | 无需文件访问 |
| Shopify 商店 | Firecrawl | 使用 Admin API 或爬取 |
| 自定义 PHP 应用 | Firecrawl | 或维护 markdown 文档 |
| 遗留站点 | Firecrawl | 无需修改 |
| 测试/POC | Firecrawl | 快速设置，无需迁移 |

## 故障排除

### 本地文件模式问题

**问题：** 没有生成文件
- **解决方案：** 检查 `contentDir` 路径是否存在
- **解决方案：** 验证 markdown 文件是否具有 `.md` 或 `.mdx` 扩展名

**问题：** 缺少描述
- **解决方案：** 在 markdown 文件中添加 frontmatter：
  ```yaml
  ---
  title: "页面标题"
  description: "页面描述"
  ---
  ```

### Firecrawl 模式问题

**问题：** API 错误
- **解决方案：** 验证是否设置了 `FIRECRAWL_API_KEY`
- **解决方案：** 检查 API 密钥是否有效
- **解决方案：** 检查 Firecrawl 积分

**问题：** 未提取内容
- **解决方案：** 验证 URL 是否可访问
- **解决方案：** 检查 robots.txt 是否允许爬取
- **解决方案：** 尝试设置 `onlyMainContent: true`

### OpenAI 增强问题

**问题：** AI 功能不工作
- **解决方案：** 安装 `openai-php/client`
- **解决方案：** 验证是否设置了 `OPENAI_API_KEY`
- **解决方案：** 检查 API 密钥是否有积分

**问题：** 描述质量差
- **解决方案：** 尝试不同的模型（使用 gpt-4o 而不是 gpt-4o-mini）
- **解决方案：** 增加 `max_tokens`
- **解决方案：** 改进内容质量
