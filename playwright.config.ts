import { PlaywrightTestConfig } from '@playwright/test';
const config: PlaywrightTestConfig = {
	testDir: "tests/Full",
	testMatch: "**/*.spec.ts",
  use: {
    // Browser options

    // Context options
    ignoreHTTPSErrors: true,
  },
	projects: [
    {
      name: 'Chromium',
      use: { browserName: 'chromium' },
    },

    {
      name: 'Firefox',
      use: { browserName: 'firefox' },
    },

    {
      name: 'WebKit',
      use: { browserName: 'webkit' },
    },
  ],
};
export default config;
