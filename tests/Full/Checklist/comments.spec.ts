import { test, expect } from "@playwright/test";
import { login } from "../utilities";

test("checklist comments", async ({ page }) => {
	await login(page);

	// Open the existing "Koalati" project
	await page.locator('[aria-label="Open\\ project\\ Koalati"]').click();

	// Open the checklist
	await page.click("#sidebar >> text=Checklist");

	// Get the first checklist item
	const checklistItem = page.locator('checklist-item-list .nb--list-item').first();
	const itemTitle = await checklistItem.locator("[nb-column='title'] nb-markdown").innerText();

	// Click the Learn more link
	await checklistItem.locator('text=Learn more').first().click();

	// Check that the sidepanel opened with the right info
	const sidepanel = page.locator("nb-sidepanel");
	await expect(sidepanel, "Sidepanel appears when we click Learn more").toBeVisible();
	await expect(sidepanel, "Sidepanel contains item title").toContainText(itemTitle);

	// Add a comment
	const commentEditor = page.locator("#comment-editor");
	await commentEditor.type("Here's something we should fix:", { delay: 20 });
	await commentEditor.press("Enter");
	await commentEditor.type("The homepage won't load!!!", { delay: 20 });
	await commentEditor.press('Meta+Shift+ArrowLeft');
	await commentEditor.press('Meta+b');
	await sidepanel.locator("text=Add comment").click({ timeout: 2000 });

	// Wait for the success message to appear
	await page.waitForSelector("text=Your comment has been sent!");

	// Wait for the new comment to appear
	await page.waitForSelector("user-comment >> text=Here's something we should fix");

	// Check that the comment has been created
	const comment = sidepanel.locator("user-comment");
	const boldedLine = comment.locator("strong >> text=The homepage won't load!!!");
	await expect(boldedLine, "Comment contains bolded text").toBeVisible();

	// Check that the item's comment indicator has been updated
	await expect(checklistItem, "Checklist item comment link is updated upon comment creation").toContainText("1 unresolved comment");

	// Reply to the first comment
	await comment.locator("nb-button >> text=Reply").click({ timeout: 1000 });
	const replyEditor = comment.locator("#comment-editor");
	await replyEditor.type("I fixed it!", { delay: 20 });
	await comment.locator("text=Submit reply").click({ timeout: 2000 });

	// Wait for the success message to appear
	await page.waitForSelector("text=Your comment has been sent!");

	// Wait for the new comment to appear
	await page.waitForSelector("user-comment >> text=I fixed it!");

	// Check that the item's comment indicator still says 1 comment (replies don't count as unresolved comments)
	await expect(checklistItem, "Checklist item comment link is not updated upon reply").toContainText("1 unresolved comment");

	// Resolve the thread
	await comment.locator("nb-button >> text=Resolve").click({ timeout: 2000 });
	await page.waitForSelector("user-comment >> text=Resolved");

	// Check that the item's comment indicator still says 1 comment (replies don't count as unresolved comments)
	await expect(checklistItem, "Checklist item comment link is updated upon comment resolution").toContainText("2 comments");
});
