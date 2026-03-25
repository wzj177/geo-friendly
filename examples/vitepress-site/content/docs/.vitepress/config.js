// VitePress Configuration
module.exports = {
  title: 'My VitePress Documentation',
  description: 'Technical documentation built with VitePress',

  base: '/',

  // Ignore dead links for external URLs
  ignoreDeadLinks: true,

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

    // Disable search for this example (requires Algolia credentials)
    // search: {
    //   provider: 'algolia'
    // }
  },

  locales: {
    root: {
      label: '简体中文',
      lang: 'zh-CN'
    }
  }
}
