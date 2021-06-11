import { test, expect } from "@playwright/test";
import { login } from "../utilities";

test.describe("login flow", () => {
	test("redirects to login page when no session is active", async ({ page }) => {
		await page.goto("https://localhost/");
		expect(await page.evaluate(() => window.location.pathname)).toBe("/login")
	});

	test("prevents login from inexistant account", async ({ page }) => {
		await login(page, "bad@email.com", "notapassword");
		await page.waitForSelector("form .error");
		expect(await (await page.$(".error")).textContent()).toBe("The email address or password you entered was invalid.");
	});

	test("allows login from regular account", async ({ page }) => {
		await login(page);
		expect(await page.$("#page-wrapper")).toBeTruthy();
	});

	test("allows logout from within the app", async ({ page }) => {
		await login(page);
		await page.waitForSelector("a[href='/logout']", { state: "attached" });
		await page.hover("#profile-toggle");
		await Promise.all([page.waitForNavigation(), page.click("a[href='/logout']")]);
		expect(await page.evaluate(() => window.location.pathname)).toBe("/login")
	});
});
