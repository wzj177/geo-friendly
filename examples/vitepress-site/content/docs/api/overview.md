---
title: "API 概览"
description: "Geo-Friendly API 完整参考文档"
tags: [api, reference]
category: api
---

# API 概览

Geo-Friendly 提供了完整的 API 用于生成 AI 友好的文件。

## 核心 API

### GeoFriendly 类

主入口类，用于生成所有 GEO 文件。

```php
use GeoFriendly\GeoFriendly;

$geo = new GeoFriendly($config);
[$generated, $errors] = $geo->generate();
```

### 配置选项

| 选项 | 类型 | 默认值 | 描述 |
|------|------|--------|------|
| title | string | "My Site" | 网站标题 |
| url | string | "https://example.com" | 网站 URL |
| contentDir | string | "./content" | 内容目录 |
| outDir | string | "./public" | 输出目录 |

## 生成器

### RobotsTxtGenerator

生成 robots.txt 文件。

### LlmsTxtGenerator

生成 llms.txt 文件用于 LLM 发现。

### SitemapGenerator

生成 XML sitemap。

## 更多信息

查看 [API 参考](/api/reference) 了解详细信息。
