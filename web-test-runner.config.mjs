import {playwrightLauncher} from "@web/test-runner-playwright";

const browsers = {
	// Local browser testing via playwright
	chromium: playwrightLauncher({product: "chromium"}),
	firefox: playwrightLauncher({product: "firefox"}),
	webkit: playwrightLauncher({product: "webkit"}),
};

// Prepend BROWSERS=x,y to `npm run test` to run a subset of browsers
// e.g. `BROWSERS=chromium,firefox npm run test`
const noBrowser = (b) => {
	throw new Error(`No browser configured named '${b}'; using defaults`);
};
let commandLineBrowsers;
try {
	// eslint-disable-next-line no-undef
	if (typeof process.env.BROWSERS !== "undefined") {
		// eslint-disable-next-line no-undef
		commandLineBrowsers = process.env.BROWSERS.split(",").map(
			(b) => (typeof browsers[b] != "undefined" ? browsers[b] : noBrowser(b))
		);
	}
} catch (e) {
	console.warn(e);
}

// https://modern-web.dev/docs/test-runner/cli-and-configuration/
export default {
	rootDir: ".",
	files: ["./tests/WebComponent/**/*.test.js"],
	nodeResolve: true,
	preserveSymlinks: true,
	browsers: commandLineBrowsers || Object.values(browsers),
	testFramework: {
		// https://mochajs.org/api/mocha
		config: {
			ui: "bdd",
		},
	},
	plugins: [],
};
