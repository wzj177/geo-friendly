# 通用后台管理系统集成方案

本文档详细说明如何为不支持 CMS 的项目设计一个符合 GEO（生成式引擎优化）规范的后台管理系统。

## 方案概述

### 核心理念

**后台编辑器 → Geo-Friendly 包 → GEO 文件 → AI 引擎**

1. **后台编辑**: 用 Markdown 编辑器维护内容
2. **结构化存储**: 按照规范存储内容元数据
3. **文件生成**: 使用 Geo-Friendly 包生成标准 GEO 文件
4. **AI 发现**: ChatGPT、Claude、Perplexity 等引擎发现和引用

### 适用场景

- ✅ **SaaS 应用** - 需要为每个租户生成 SEO 文件
- ✅ **企业后台** - 需要维护大量静态/半静态页面
- ✅ **多语言站点** - 需要为不同语言生成对应的 GEO 文件
- ✅ **微服务架构** - 各服务独立管理自己的文档
- ✅ **内部系统** - 外部不可访问，需要通过 API 生成 GEO

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
    site_id BIGINT UNSIGNED NOT NULL,
    type ENUM('page', 'article', 'api', 'guide') NOT NULL,

    -- 基本信息
    slug VARCHAR(255) NOT NULL,
    url VARCHAR(512) NOT NULL,

    -- GEO 必需字段
    title VARCHAR(255) NOT NULL,
    description TEXT,
    keywords VARCHAR(512),

    -- 内容
    content MEDIUMTEXT,
    html_content MEDIUMTEXT,

    -- 分类和标签
    category VARCHAR(100),
    tags JSON,

    -- SEO 元数据
    meta_title VARCHAR(255),
    meta_description VARCHAR(512),
    og_title VARCHAR(255),
    og_description VARCHAR(512),
    og_image VARCHAR(512),

    -- 多语言支持
    language CHAR(2) DEFAULT 'zh',
    translation_id BIGINT UNSIGNED,

    -- 状态
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at DATETIME,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_site_status (site_id, status),
    UNIQUE KEY uk_site_slug (site_id, slug)
);
```

## 后台界面设计

### 内容编辑界面

```
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
│  │  Markdown 编辑器                                 │      │
│  │                                                 │      │
│  └─────────────────────────────────────────────────┘      │
│                                                            │
│  [保存草稿]  [预览]  [发布并生成 GEO]                      │
└────────────────────────────────────────────────────────────┘
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
  ]
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

## 部署方案

### 方案 1: 代理模式

将生成的 GEO 文件通过 API 提供给前端：

```nginx
location /llms.txt {
    proxy_pass http://backend/api/geo/files/llms.txt;
    proxy_set_header Content-Type "text/plain; charset=utf-8";
}
```

### 方案 2: 同步模式

定期生成文件并同步到 CDN 或服务器。

### 方案 3: 混合模式

热数据从 API 获取，冷数据使用缓存。

## 最佳实践

### 1. 内容规范

**标题规范**:
- 长度：3-4 个词
- 格式：`主标题 - 副标题`
- 示例：`快速开始 - VitePress 文档`

**描述规范**:
- 长度：9-10 个词
- 内容：包含关键词和核心价值
- 示例：`5分钟快速上手 VitePress 静态站点生成器`

### 2. 性能优化

- **缓存策略**: 生成文件后缓存 1-24 小时
- **按需生成**: 只在内容更新时重新生成
- **队列处理**: 大量内容时使用队列异步生成

### 3. 安全考虑

- **API 认证**: 所有 GEO 生成接口需要认证
- **权限控制**: 只允许站点管理员生成文件
- **敏感数据**: 不要在 GEO 文件中包含敏感信息

## 总结

通用后台管理方案的核心是：

1. **结构化存储**: 在数据库中维护符合 GEO 规范的内容
2. **灵活生成**: 使用 Geo-Friendly 包按需生成标准文件
3. **多种部署**: 支持代理、同步、混合等多种部署模式
4. **完整集成**: 从编辑到生成的完整工作流

这个方案既保持了灵活性，又遵循了 GEO 的标准规范，适合各种类型的项目。
