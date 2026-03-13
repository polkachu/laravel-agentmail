// @ts-check

/** @type {import('@docusaurus/plugin-content-docs').SidebarsConfig} */
const sidebars = {
  tutorialSidebar: [
    { type: 'doc', id: 'introduction', label: 'Introduction' },
    { type: 'doc', id: 'installation', label: 'Installation' },
    { type: 'doc', id: 'configuration', label: 'Configuration' },
    {
      type: 'category',
      label: 'Usage',
      items: ['usage/inboxes', 'usage/messages', 'usage/error-handling'],
    },
  ],
};

export default sidebars;
