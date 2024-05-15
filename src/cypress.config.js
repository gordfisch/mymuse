const { defineConfig } = require('cypress');
const setupPlugins = require('./tests/System/plugins/index');

module.exports = defineConfig({
  fixturesFolder: 'tests/System/fixtures',
  videosFolder: 'tests/System/output/videos',
  screenshotsFolder: 'tests/System/output/screenshots',
  viewportHeight: 1000,
  viewportWidth: 1200,
  e2e: {
    setupNodeEvents(on, config) {
      setupPlugins(on, config);
    },
    baseUrl: 'http://localhost/mymuse/joomla-cms/',
    specPattern: [
      'tests/System/integration/install/**/*.cy.{js,jsx,ts,tsx}',
      'tests/System/integration/administrator/**/*.cy.{js,jsx,ts,tsx}',
      'tests/System/integration/site/**/*.cy.{js,jsx,ts,tsx}',
      'tests/System/integration/api/**/*.cy.{js,jsx,ts,tsx}',
      'tests/System/integration/plugins/**/*.cy.{js,jsx,ts,tsx}',
    ],
    supportFile: 'tests/System/support/index.js',
    scrollBehavior: 'center',
    browser: 'firefox',
    screenshotOnRunFailure: true,
    video: false,
  },
  env: {
    sitename: 'MyMuse Joomla CMS Test',
    name: 'jane doe',
    email: 'info@arboreta.ca',
    username: 'joomlatest',
    password: 'FrQgkUHx6Jyh8F9',
    db_type: 'MySQLi',
    db_host: 'localhost',
    db_port: '',
    db_name: 'mymuse_joomla5',
    db_user: 'root',
    db_password: 'dylan',
    db_prefix: 'p9ifu_',
    smtp_host: 'localhost',
    smtp_port: '1025',
    cmsPath: '.',
  },
});
