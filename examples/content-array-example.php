<?php
/**
 * Content Array Example
 *
 * This example shows how to use Geo-Friendly with content stored in a database.
 */

require __DIR__ . '/../vendor/autoload.php';

use GeoFriendly\GeoFriendly;

// Example: Content from database
// In a real application, you would fetch this using:
// $contents = Content::where('status', 'published')->get()->toArray();

$contents = [
    [
        'title' => '快速开始',
        'url' => '/getting-started',
        'content' => "# 快速开始\n\n这是一个快速开始指南。\n\n## 安装\n\n```bash\ncomposer require wzj177/geo-friendly\n```\n",
        'description' => '5分钟快速上手 Geo-Friendly',
        'category' => '指南',
        'tags' => ['入门', '教程'],
    ],
    [
        'title' => 'API 参考',
        'url' => '/api/reference',
        'content' => "# API 参考\n\n完整的 API 文档。\n\n## GeoFriendly 类\n\n主入口类。\n",
        'description' => '完整的 API 参考文档',
        'category' => 'API',
        'tags' => ['API', '参考'],
    ],
    [
        'title' => '配置选项',
        'url' => '/configuration/options',
        'content' => "# 配置选项\n\n详细的配置说明。\n\n## 基本配置\n\ntitle, url, outDir 等选项。\n",
        'description' => 'Geo-Friendly 配置选项说明',
        'category' => '指南',
        'tags' => ['配置'],
    ],
];

// Configure Geo-Friendly
$config = [
    'title' => '我的文档',
    'url' => 'https://docs.example.com',
    'description' => '技术文档和 API 参考',
    'outDir' => __DIR__ . '/output',
    'contents' => $contents,  // Pass content array directly
];

// Generate GEO files
echo "Generating GEO files from content array...\n\n";

$geo = new GeoFriendly($config);
[$generated, $errors] = $geo->generate();

// Display results
echo "Generated files:\n";
foreach ($generated as $filename) {
    $filepath = $config['outDir'] . '/' . $filename;
    $size = file_exists($filepath) ? filesize($filepath) : 0;
    echo "  ✓ {$filename} ({$size} bytes)\n";
}

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "  ✗ {$error}\n";
    }
}

echo "\nOutput directory: {$config['outDir']}\n";
echo "\nGenerated llms.txt:\n";
echo str_repeat('=', 60) . "\n";
echo file_get_contents($config['outDir'] . '/llms.txt');
echo str_repeat('=', 60) . "\n";
