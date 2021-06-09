import { folio as baseFolio } from "@playwright/test";
import { BrowserContextOptions } from "playwright";

const builder = baseFolio.extend();

builder.contextOptions.override(async ({ contextOptions }, runTest) => {
  const modifiedOptions: BrowserContextOptions = {
    ...contextOptions, // default options
    ignoreHTTPSErrors: true
  }
  await runTest(modifiedOptions);
});

const folio = builder.build();

export const it = folio.it;
export const expect = folio.expect;
export const describe = folio.describe;
