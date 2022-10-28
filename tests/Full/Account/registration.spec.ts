import { test, expect } from "@playwright/test";

test.describe("registration flow", () => {
	test("sign up for an account from the login page", async ({ page }) => {
		// Start at the login screen
		await page.goto("https://localhost/");

		// Navigation to the registration page
		await page.click("text=Create an account");
		expect(await page.evaluate(() => window.location.pathname)).toBe("/sign-up");

		// Fill the regisration form
		const randomSuffix = Math.random().toString(36).replace(/[^a-z]+/g, "").substring(0, 5);
		await page.fill("text=name", "John");
		await page.fill("text=email", `test+${randomSuffix}@koalati.com`);
		await page.fill("text=password", "123456");
		await page.click("text=Create my account");
		await page.waitForNavigation();

		// At this point, the user should be on the "Confirm your email" page
		expect(await page.locator("h1").textContent()).toContain("Confirm your email");
	});
});
