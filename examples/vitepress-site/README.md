# VitePress + Geo-Friendly 集成示例

这个示例展示如何在 VitePress 静态站点中集成 Geo-Friendly，使用本地 markdown 文件生成 GEO 文件。

## 目录结构

```
vitepress-site/
├── content/
│   └── docs/
│       ├── index.md              # 首页
│       ├── getting-started.md    # 入门指南
│       ├── api/
│       │   ├── overview.md
│       │   └── reference.md
│       └── guide/
│           ├── installation.md
│           └── configuration.md
├── config/
│   ├── docs/
│   │   └── .vitepress/
│   │       └── config.js
│   └── docs/public/              # GEO 文件输出目录
├── geofriendly.yaml              # Geo-Friendly 配置
├── package.json
└── README.md
```

## 工作流程

### 1. 在 content/docs 中维护 Markdown 文件

所有文档内容以 markdown 格式保存在 `content/docs/` 目录中。

**示例：getting-started.md**
```markdown
---
title: "快速开始"
description: "5分钟快速上手 VitePress 和 Geo-Friendly"
---

# 快速开始

这是一个 VitePress 文档站点...
```

### 2. 配置 VitePress

在 `config/docs/.vitepress/config.js` 中：

```javascript
module.exports = {
  title: 'My Documentation',
  description: 'Technical documentation',
  base: '/',
  srcDir: '../../content/docs',
  outDir: '../../config/docs/docs-dist',
  themeConfig: {
    // your theme config
  }
}
```

### 3. 生成 GEO 文件

```bash
# 开发时生成
npm run geo:generate

# 或者构建时自动生成
npm run docs:build && npm run geo:generate
```

### 4. 将 GEO 文件部署到网站

生成的 GEO 文件在 `config/docs/public/` 目录：

```
public/
├── robots.txt
├── llms.txt
├── llms-full.txt
├── sitemap.xml
├── docs.json
├── ai-index.json
└── schema.json
```

将这些文件复制到你的网站根目录即可。

## 自动化脚本

在 `package.json` 中添加构建脚本：

```json
{
  "scripts": {
    "build": "npm run geo:generate && vitepress build config",
    "dev": "vitepress dev config",
    "geo:generate": "php ../../bin/geo generate --config=geofriendly.yaml"
  }
}
```

## VitePress 配置示例

```javascript
// config/docs/.vitepress/config.js
const { description } = require('../../geofriendly.yaml');

module.exports = {
  title: 'My VitePress Docs',
  description: description,
  locales: {
    root: {
      label: 'English',
      lang: 'en-US'
    },
    zh: {
      label: '简体中文',
      lang: 'zh-CN'
    }
  },
  themeConfig: {
    nav: [
      { text: 'Guide', link: '/guide/' },
      { text: 'API', link: '/api/' }
    ],
    sidebar: {
      '/guide/': [
        {
          text: 'Getting Started',
          items: [
            { text: 'Installation', link: '/guide/installation' },
            { text: 'Configuration', link: '/guide/configuration' }
          ]
        }
      ]
    }
  }
}
```

## 内容维护建议

### Frontmatter 格式

每个 markdown 文件应该包含 frontmatter：

```yaml
---
title: "页面标题"
description: "页面描述，用于 SEO 和 AI 理解"
tags: [tag1, tag2]
category: documentation
---

# 内容开始
```

### 目录组织

```
content/docs/
├── index.md                    # 首页
├── getting-started.md          # 入门
├── guide/                      # 指南
│   ├── installation.md
│   └── configuration.md
├── api/                        # API 参考
│   ├── overview.md
│   └── reference.md
└── advanced/                   # 高级主题
    └── deployment.md
```

### 生成时机

- **开发环境**: 手动运行 `npm run geo:generate`
- **生产构建**: 在 CI/CD 中自动生成
- **内容更新**: 每次 markdown 文件更新后重新生成

## CI/CD 集成

在 GitHub Actions 中自动生成：

```yaml
name: Build and Deploy

on:
  push:
    branches: [main]

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install Geo-Friendly
        run: composer require wzj177/geo-friendly

      - name: Generate GEO files
        run: php vendor/bin/geo generate --config=examples/vitepress-site/geofriendly.yaml

      - name: Build VitePress
        run: npm run build

      - name: Deploy
        # your deployment steps
```

## 验证 GEO 文件

生成后，访问以下 URL 验证：

```
https://your-domain.com/llms.txt
https://your-domain.com/sitemap.xml
https://your-domain.com/docs.json
```

## 优势

- ✅ 静态文件，快速生成
- ✅ 版本控制，可追溯
- ✅ 离线可用，无 API 依赖
- ✅ 与 VitePress 工作流无缝集成
- ✅ 支持 VitePress 的多语言和主题
