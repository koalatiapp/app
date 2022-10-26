import { test, expect } from "@playwright/test";
import { login } from "../utilities";

test("creates, edits and deletes a project", async ({ page }) => {
	await login(page);

	// Go to project creation page through quick actions
	await page.hover("#quick-actions .toggle");
	await page.click("text=New project");
	await page.waitForSelector("text=Give your project a name");

	// Fill in the project creation form
	await page.fill("text=Give your project a name", "My Website");
	await page.fill("text=Enter your website's URL", "https://www.koalati.com");
	await page.click("nb-button:has-text('Create project')");

	// Wait for confirmation message
	await page.waitForSelector("text=has been created successfully");
	expect(await page.evaluate(() => window.location.pathname)).toMatch(/^\/project\/[a-zA-Z0-9]+\//);

	// Go to project settings
	await page.click("#sidebar li.active a:has-text('Settings')");

	// Update the project's name
	await page.fill("text=Project name", "My New Website");
	await page.click("text=Update project");
	await page.waitForSelector("text=has been updated successfully");
	expect(await page.$("text=My New Website")).toBeTruthy();

	// Delete the project
	await page.click("a:has-text('Deletion')");
	await page.click("text=I am certain that I want to delete the project");
	await page.click("text=Delete this project");
	await page.waitForSelector("text=has been deleted successfully");
	expect(await page.evaluate(() => window.location.pathname)).toBe("/projects");
});
