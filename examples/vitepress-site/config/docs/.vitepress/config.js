// VitePress Configuration
const fs = require('fs');
const yaml = require('js-yaml');

// Load Geo-Friendly config
const geoConfig = yaml.load(
  fs.readFileSync(__dirname + '/../../../geofriendly.yaml', 'utf8')
);

module.exports = {
  title: geoConfig.title || 'My Documentation',
  description: geoConfig.description || 'Technical Documentation',
  base: '/',

  // Content directory
  srcDir: '../../content/docs',

  // Output directory
  outDir: '../../config/docs/dist',

  // VitePress config
  markdown: {
    config: (md) => {
      // customize markdown-it
    }
  },

  themeConfig: {
    nav: [
      { text: '首页', link: '/' },
      { text: '快速开始', link: '/getting-started' },
      { text: 'API', link: '/api/overview' }
    ],

    sidebar: [
      {
        text: '指南',
        items: [
          { text: '快速开始', link: '/getting-started' }
        ]
      },
      {
        text: 'API',
        items: [
          { text: '概览', link: '/api/overview' }
        ]
      }
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/yourusername/repo' }
    ],

    search: {
      provider: 'algolia',
      options: {
        appId: 'YOUR_APP_ID',
        apiKey: 'YOUR_API_KEY',
        indexName: 'INDEX_NAME'
      }
    }
  },

  locales: {
    root: {
      label: '简体中文',
      lang: 'zh-CN'
    }
  }
}
