import { test, expect } from "@playwright/test";
import { login } from "../utilities";

test("creates, edits and remove ourselves from an organization", async ({ page }) => {
	await login(page);

	// Click the team creation link, wherever it is found
	await page.click("text=Team");
	await page.hover("#organization-selector-wrapper");
	await page.click("[href='/team/create']");

	// Fill in the team creation form
	await page.fill("text=Team name", "My Test Team");
	await page.click("text=Create your team");

	// Wait for confirmation message
	await page.waitForSelector("text=has been created successfully");
	expect(await page.evaluate(() => window.location.pathname)).toMatch(/^\/team\/[a-zA-Z0-9]+/);

	// Go to team settings
	await page.click("#sidebar li.active a:has-text('Settings')");

	// Update the project's name
	await page.fill("text=Team name", "My Updated Test Team");
	await page.click("text=Save changes");
	await page.waitForSelector("text=has been updated successfully");
	expect(await page.$("text=My Updated Test Team")).toBeTruthy();

	// Remove ourselves from the organization
	let confirmationDialogsAccepted = 0;
	await page.getByRole("tab", { name: "Members" }).click();
	page.on("dialog", dialog => {
		confirmationDialogsAccepted += 1;
		dialog.accept();
	});
	await page.getByRole("button", { name: "Remove from team" }).click({ force: true });
	await page.waitForURL("**/projects");
	expect(confirmationDialogsAccepted, "Two confirmation dialogs should be accepted to remove the last member of an organization").toBe(2);
});
