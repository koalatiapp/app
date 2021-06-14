import { PlaywrightTestConfig, expect } from "@playwright/test";
import { matchers } from "expect-playwright";

expect.extend(matchers);

const config: PlaywrightTestConfig = {
	testDir: "tests/Full",
	testMatch: "**/*.spec.ts",
	timeout: 60000,
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
