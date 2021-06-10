import { test, expect } from "@playwright/test";

test.describe("login flow", () => {
	test("redirects to login page when no session is active", async ({ page }) => {
		await page.goto("https://localhost/");
		expect(await page.evaluate(() => window.location.pathname)).toBe("/login")
	});

	test("prevents login from inexistant account", async ({ page }) => {
		await page.goto("https://localhost/");
		await page.fill("#input-email input", "bad@email.com");
		await page.fill("#input-password input", "notapassword");
		await page.click("nb-button[type='submit']");
		await page.waitForSelector("form .error", { timeout: 5000 });
		expect(await (await page.$(".error")).textContent()).toBe("The email address or password you entered was invalid.");
	});

	test("allows login from regular account", async ({ page }) => {
		await page.goto("https://localhost/");
		await page.fill("#input-email input", "name@email.com");
		await page.fill("#input-password input", "123456");
		await Promise.all([page.waitForNavigation({ timeout: 5000 }), page.click("nb-button[type='submit']")]);
		expect(await page.$("#page-wrapper")).toBeTruthy();
	});

	test("allows logout from within the app", async ({ page }) => {
		await page.goto("https://localhost/");
		await page.fill("#input-email input", "name@email.com");
		await page.fill("#input-password input", "123456");
		await Promise.all([page.waitForSelector("a[href='/logout']", { state: "attached", timeout: 5000 }), page.click("nb-button[type='submit']")]);
		await page.hover("#profile-toggle");
		await Promise.all([page.waitForNavigation({ timeout: 5000 }), page.click("a[href='/logout']")]);
		expect(await page.evaluate(() => window.location.pathname)).toBe("/login")
	});
});
