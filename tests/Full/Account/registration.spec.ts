import { test, expect } from "@playwright/test";
import { login } from "../utilities";

test.describe("registration flow", () => {
	test("sign up for an account from the login page", async ({ page }) => {
		// Start at the login screen
		await page.goto("https://localhost/");

		// Navigation to the registration page
		await page.click("text=Create an account");
		expect(await page.evaluate(() => window.location.pathname)).toBe("/sign-up")

		// Fill the regisration form
		const randomSuffix = Math.random().toString(36).replace(/[^a-z]+/g, '').substring(0, 5);
		await page.fill("text=name", "John");
		await page.fill("text=email", `test+${randomSuffix}@koalati.com`);
		await page.fill("text=password", "123456");
		await page.click("text=Create my account");
		await page.waitForSelector("a[href='/logout']");

		// Check that the registration worked
		expect(await page.locator("a[href='/logout']").count()).toBeGreaterThan(0);
	});
});
