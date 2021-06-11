import { Page } from "@playwright/test"

const login = async (page: Page, email: string = "name@email.com", password: string = "123456") => {
	await page.goto("https://localhost/");
	await page.fill("#input-email input", email);
	await page.fill("#input-password input", password);
	await Promise.all([page.waitForNavigation(), page.click("nb-button:has-text('Sign in')")]);
};

export { login };
