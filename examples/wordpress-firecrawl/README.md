# WordPress + Firecrawl + Geo-Friendly 集成示例

这个示例展示如何使用 Geo-Friendly 和 Firecrawl 为 WordPress 站点自动生成 GEO 文件。

## 场景说明

当你有一个 WordPress 站点，但不想修改 WordPress 代码或安装插件时，可以使用这个方案：

1. **不修改 WordPress** - 无需安装插件或修改主题
2. **外部爬取** - 使用 Firecrawl API 爬取公开内容
3. **独立部署** - GEO 文件可以部署到 CDN 或其他服务器
4. **自动化** - 通过 cron 定期自动更新

## 工作原理

```
┌─────────────┐         ┌────────────┐         ┌──────────────┐
│  WordPress  │────────▶│ Firecrawl  │────────▶│ Geo-Friendly │
│   (源站)    │  公开   │  (爬虫)    │  转换   │  (生成)      │
└─────────────┘         └────────────┘         └──────────────┘
                                                      │
                                                      ▼
                                              ┌──────────────┐
                                              │  GEO 文件     │
                                              │  (CDN/源站)   │
                                              └──────────────┘
```

## 目录结构

```
wordpress-firecrawl/
├── config/
│   └── geofriendly.yaml          # Geo-Friendly 配置
├── output/                       # 生成的 GEO 文件
├── scripts/
│   ├── generate.php              # 生成脚本
│   └── deploy.sh                 # 部署脚本（可选）
├── .env                          # 环境变量
├── .env.example                  # 环境变量示例
├── composer.json
└── README.md
```

## 安装步骤

### 1. 克隆或创建项目

```bash
mkdir wordpress-geo
cd wordpress-geo
```

### 2. 安装依赖

```bash
composer require wzj177/geo-friendly
```

### 3. 配置环境变量

复制 `.env.example` 到 `.env`：

```bash
cp .env.example .env
```

编辑 `.env` 文件：

```bash
# WordPress 站点配置
WP_URL=https://your-site.com
WP_TITLE=Your Site Name
WP_DESCRIPTION=Your site description

# Firecrawl API 配置
FIRECRAWL_API_KEY=fc-your-api-key-here

# 输出目录
OUTPUT_DIR=./output

# 可选：部署配置（如果使用 FTP/SCP 部署）
DEPLOY_HOST=your-server.com
DEPLOY_USER=username
DEPLOY_PATH=/path/to/wordpress/root
```

### 4. 配置 Geo-Friendly

编辑 `config/geofriendly.yaml`：

```yaml
title: '%env(WP_TITLE)%'
url: '%env(WP_URL)%'
description: '%env(WP_DESCRIPTION)%'

# 输出目录
outDir: '%env(OUTPUT_DIR)%'

# 留空 contentDir - 使用 Firecrawl 模式
contentDir: ''

# 启用 Firecrawl
firecrawl:
  apiKey: '%env(FIRECRAWL_API_KEY)%'
  apiUrl: 'https://api.firecrawl.dev/v1'
  enabled: true

# 可选：启用 OpenAI 增强
openai:
  apiKey: '%env(OPENAI_API_KEY)%'
  enabled: false

# 生成器配置
generators:
  robotsTxt: true
  llmsTxt: true
  llmsFullTxt: true
  sitemap: true
  docsJson: true
  aiIndex: true
  schema: true
```

## 使用方法

### 手动生成

```bash
php scripts/generate.php
```

### 查看生成的文件

```bash
ls -la output/
```

你会看到：

```
output/
├── robots.txt
├── llms.txt
├── llms-full.txt
├── sitemap.xml
├── docs.json
├── ai-index.json
└── schema.json
```

### 部署到 WordPress

#### 方式 1: 手动上传

使用 FTP/SFTP 将 `output/` 目录中的文件上传到 WordPress 根目录。

#### 方式 2: 使用 WP-CLI（推荐）

如果你有服务器 SSH 访问权限：

```bash
# 使用 SCP 上传
scp output/* user@your-server.com:/path/to/wordpress/

# 或者使用 rsync
rsync -avz output/ user@your-server.com:/path/to/wordpress/
```

#### 方式 3: 使用 WordPress REST API

如果你有 WordPress 管理员权限，可以通过 REST API 上传：

```bash
php scripts/deploy-via-api.php
```

## 自动化生成

### 使用 Cron 定期生成

在 crontab 中添加：

```bash
# 每天凌晨 2 点生成 GEO 文件
0 2 * * * cd /path/to/wordpress-geo && php scripts/generate.php >> logs/generate.log 2>&1

# 每周日凌晨 3 点部署
0 3 * * 0 cd /path/to/wordpress-geo && bash scripts/deploy.sh >> logs/deploy.log 2>&1
```

### 使用 GitHub Actions

创建 `.github/workflows/generate-geo.yml`：

```yaml
name: Generate GEO Files

on:
  schedule:
    - cron: '0 2 * * *'  # 每天凌晨 2 点
  workflow_dispatch:      # 手动触发

jobs:
  generate:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout
        uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathuru/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install dependencies
        run: composer install --no-dev

      - name: Generate GEO files
        env:
          WP_URL: ${{ secrets.WP_URL }}
          WP_TITLE: ${{ secrets.WP_TITLE }}
          WP_DESCRIPTION: ${{ secrets.WP_DESCRIPTION }}
          FIRECRAWL_API_KEY: ${{ secrets.FIRECRAWL_API_KEY }}
        run: php scripts/generate.php

      - name: Deploy to server
        uses: easingthemes/ssh-deploy@main
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          REMOTE_HOST: ${{ secrets.REMOTE_HOST }}
          REMOTE_USER: ${{ secrets.REMOTE_USER }}
          TARGET: /path/to/wordpress/
        with:
          SOURCE: "output/"
```

## WordPress 站点配置

### 1. 确保 robots.txt 允许爬虫

在 WordPress 设置中，确保 **设置 > 阅读 > 搜索引擎可见性** 是勾选的。

### 2. 验证爬虫访问

```bash
curl -I https://your-site.com/robots.txt
```

### 3. 检查防火墙

确保你的服务器允许来自 Firecrawl 的请求。

## 故障排除

### 问题 1: Firecrawl API 错误

```
Error: Firecrawl API request failed
```

**解决方案：**
- 检查 API key 是否正确
- 验证 Firecrawl 账户是否有足够配额
- 确认网络连接正常

### 问题 2: 内容未完整爬取

```
Warning: Only partial content extracted
```

**解决方案：**
- 检查 robots.txt 是否允许爬取
- 确认网站没有登录墙或验证码
- 检查网站响应时间

### 问题 3: 生成的文件格式错误

```
Error: Invalid llms.txt format
```

**解决方案：**
- 检查 Firecrawl 返回的内容格式
- 查看日志文件了解详情
- 尝试手动运行并调试

## 生成脚本示例

`scripts/generate.php`:

```php
#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use GeoFriendly\GeoFriendly;

// 加载环境变量
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// 配置
$config = [
    'title' => $_ENV['WP_TITLE'],
    'url' => rtrim($_ENV['WP_URL'], '/'),
    'description' => $_ENV['WP_DESCRIPTION'] ?? '',
    'outDir' => $_ENV['OUTPUT_DIR'] ?? './output',
    'contentDir' => '', // 使用 Firecrawl 模式
    'firecrawl' => [
        'apiKey' => $_ENV['FIRECRAWL_API_KEY'],
        'apiUrl' => 'https://api.firecrawl.dev/v1',
        'enabled' => true,
    ],
];

echo "Generating GEO files for: {$config['url']}\n";
echo "Output directory: {$config['outDir']}\n\n";

try {
    $geo = new GeoFriendly($config);
    [$generated, $errors] = $geo->generate();

    if (!empty($generated)) {
        echo "✓ Generated files:\n";
        foreach ($generated as $file) {
            $path = $config['outDir'] . '/' . $file;
            $size = file_exists($path) ? filesize($path) : 0;
            echo "  - {$file} (" . number_format($size) . " bytes)\n";
        }
    }

    if (!empty($errors)) {
        echo "\n✗ Errors:\n";
        foreach ($errors as $error) {
            echo "  - {$error}\n";
        }
        exit(1);
    }

    echo "\n✓ Done!\n";

} catch (Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
```

## 部署脚本示例

`scripts/deploy.sh`:

```bash
#!/bin/bash

# 配置
SOURCE_DIR="./output"
SERVER="${DEPLOY_USER}@${DEPLOY_HOST}"
DEST_PATH="${DEPLOY_PATH}"

# 验证
if [ ! -d "$SOURCE_DIR" ]; then
    echo "Error: Output directory not found"
    exit 1
fi

# 部署
echo "Deploying GEO files to ${SERVER}:${DEST_PATH}"

# 使用 rsync 同步
rsync -avz --delete \
    "$SOURCE_DIR/" \
    "$SERVER:$DEST_PATH/"

echo "✓ Deployed successfully"
```

## 成本考虑

### Firecrawl API 成本

- **免费层**: 每月 500 次爬取
- **付费计划**: 根据使用量计费

### 优化建议

1. **按需生成**: 只在内容更新时生成
2. **缓存策略**: 缓存已爬取的内容
3. **增量更新**: 只爬取变化的页面
4. **定期清理**: 清理过期的缓存文件

## 下一步

- 设置自动化 CI/CD 流程
- 配置 CDN 加速 GEO 文件
- 监控 Firecrawl API 使用量
- 定期检查生成的文件质量

## 相关资源

- [Firecrawl 官方文档](https://docs.firecrawl.dev)
- [Geo-Friendly 文档](../../README.md)
- [WordPress REST API](https://developer.wordpress.org/rest-api/)
