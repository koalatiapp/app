import { it, describe, expect } from "../fixtures";

describe("login flow", () => {
	it("redirects to login page when no session is active", async ({ page }) => {
		await page.goto("http://localhost/");
		expect(await page.evaluate(() => window.location.pathname)).toBe("/login")
	});

	it("prevents login from inexistant account", async ({ page }) => {
		await page.goto("http://localhost/");
		await page.fill("#input-email input", "bad@email.com");
		await page.fill("#input-password input", "notapassword");
		await page.click("nb-button[type='submit']");
		await page.waitForSelector("form .error");
		expect(await (await page.$(".error")).textContent()).toBe("The email address or password you entered was invalid.");
	});

	it("allows login from regular account", async ({ page }) => {
		await page.goto("http://localhost/");
		await page.fill("#input-email input", "name@email.com");
		await page.fill("#input-password input", "123456");
		await Promise.all([page.waitForNavigation(), page.click("nb-button[type='submit']")]);
		expect(await page.$("#page-wrapper")).toBeTruthy();
	});

	it("allows logout from within the app", async ({ page }) => {
		await page.goto("http://localhost/");
		await page.fill("#input-email input", "name@email.com");
		await page.fill("#input-password input", "123456");
		await Promise.all([page.waitForSelector("a[href='/logout']", { state: "attached" }), page.click("nb-button[type='submit']")]);
		await page.hover("#profile-toggle");
		await Promise.all([page.waitForNavigation(), page.click("a[href='/logout']")]);
		expect(await page.evaluate(() => window.location.pathname)).toBe("/login")
	});
});
