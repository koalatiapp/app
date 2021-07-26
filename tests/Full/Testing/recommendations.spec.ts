import { test, expect } from "@playwright/test";
import { login } from "../utilities";

const stubTitle = "Add an alt attribute to all of your <img> tags to describe their content.";
const stubTitleEscaped = "Add an alt attribute to all of your `<img>` tags to describe their content.";
const stubDescriptionSnippet = "Alt text is a tenet of accessible web design."

test("testing recommendations", async ({ page }) => {
	await login(page);

	// Go to the project's testing page
	await page.click("text=View all projects");
	await page.click(".project-thumbnail:has-text('Koalati')");
	await page.click("#sidebar nav a:has-text('Recommendations')");
	await page.waitForSelector(`.nb--list-item >> text=${stubTitle}`);

	// Check the first (and only) recommendation in the list
	const recommendationItem = await page.$(`.nb--list-item`);
	expect(recommendationItem).toHaveText(stubTitleEscaped);

	// Open the details of the test recommendation
	const openDetailsBtn = await recommendationItem.$("recommendation-details-link");
	await openDetailsBtn.click();
	await page.waitForSelector("recommendation-details[aria-busy='false']");

	// Validate the contents of the details dialog
	const detailsModal = await page.$("recommendation-details");
	expect(await detailsModal.$(`text=${stubTitle}`)).toBeTruthy();
	expect(await detailsModal.$(`text=${stubDescriptionSnippet}`)).toBeTruthy();
	expect(await detailsModal.$(`text=Homepage - Koalati`)).toBeTruthy();
	expect(await detailsModal.$(`text=About - Koalati`)).toBeTruthy();
});
