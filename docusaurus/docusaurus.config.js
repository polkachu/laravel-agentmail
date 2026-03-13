// @ts-check
import {themes as prismThemes} from 'prism-react-renderer';

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'Laravel AgentMail',
  tagline: 'Programmatic email inboxes for AI agents',
  favicon: 'img/favicon.ico',

  future: {
    v4: true,
  },

  url: 'https://agentmail.polkachu.com',
  baseUrl: '/',

  organizationName: 'polkachu',
  projectName: 'laravel-agentmail',

  onBrokenLinks: 'throw',

  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },

  presets: [
    [
      'redocusaurus',
      {
        specs: [
          {
            id: 'agentmail-api',
            spec: './openapi/openapi.yaml',
            route: '/api',
          },
        ],
        theme: {
          primaryColor: '#7C3AED',
        },
      },
    ],
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          sidebarPath: './sidebars.js',
        },
        blog: false,
        theme: {
          customCss: './src/css/custom.css',
        },
      }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      colorMode: {
        respectPrefersColorScheme: true,
      },
      navbar: {
        title: 'Laravel AgentMail',
        items: [
          {
            type: 'docSidebar',
            sidebarId: 'tutorialSidebar',
            position: 'left',
            label: 'Docs',
          },
          {
            to: '/api',
            label: 'API Reference',
            position: 'left',
          },
          {
            href: 'https://github.com/polkachu/laravel-agentmail',
            label: 'GitHub',
            position: 'right',
          },
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Docs',
            items: [
              { label: 'Introduction', to: '/docs/introduction' },
              { label: 'Installation', to: '/docs/installation' },
              { label: 'Configuration', to: '/docs/configuration' },
            ],
          },
          {
            title: 'More',
            items: [
              { label: 'API Reference', to: '/api' },
              {
                label: 'GitHub',
                href: 'https://github.com/polkachu/laravel-agentmail',
              },
              {
                label: 'AgentMail',
                href: 'https://agentmail.to',
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} Polkachu. Built with Docusaurus.`,
      },
      prism: {
        theme: prismThemes.github,
        darkTheme: prismThemes.dracula,
        additionalLanguages: ['php', 'bash'],
      },
    }),
};

export default config;
