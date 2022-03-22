import { test, expect } from "@playwright/test";
import { login, createProject, deleteProject } from "../utilities";

let projectId;

test.describe("checklist", () => {
	test.beforeEach(async ({ page }) => {
		await login(page);
		projectId = await createProject(page);
	});

	test("checklist comments", async ({ page }) => {
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
		const commentEditor = page.locator(".comment-editor");
		await commentEditor.type("Here's something we should fix:", { delay: 20 });
		await commentEditor.press("Enter");
		await commentEditor.type("The homepage won't load!!!", { delay: 20 });
		await sidepanel.locator("text=Add comment").click({ timeout: 2000 });

		// Wait for the success message to appear
		await page.waitForSelector("text=Your comment has been sent!", { timeout: 5000 });

		// Wait for the new comment to appear
		await page.waitForSelector("user-comment", { timeout: 5000 });

		// Check that the comment has been created
		const comment = sidepanel.locator("user-comment");
		await expect(comment, "New comment is visible").toBeVisible();

		// Check that the item's comment indicator has been updated
		await expect(checklistItem, "Checklist item comment link is updated upon comment creation").toContainText("1 unresolved comment");

		// Reply to the first comment
		await comment.locator("nb-button >> text=Reply").click({ timeout: 1000 });
		const replyEditor = comment.locator(".comment-editor");
		// Using `evaluate()` instead of `type()` here because of a bug with Playwright and Firefox
		replyEditor.evaluate(replyEditor => {
			replyEditor.innerHTML = "<div>I fixed it!</div>";
		});
		await comment.locator("text=Submit reply").click({ timeout: 2000 });

		// Wait for the success message to appear
		await page.waitForSelector("text=Your comment has been sent!", { timeout: 5000 });

		// Wait for the new comment to appear
		await page.waitForSelector("user-comment >> text=I fixed it!", { timeout: 5000 });

		// Check that the item's comment indicator still says 1 comment (replies don't count as unresolved comments)
		await expect(checklistItem, "Checklist item comment link is not updated upon reply").toContainText("1 unresolved comment");

		// Resolve the thread
		await comment.locator("nb-button >> text=Resolve").click({ timeout: 2000 });
		await page.waitForSelector("user-comment >> text=Resolved", { timeout: 5000 });

		// Check that the item's comment indicator still says 1 comment (replies don't count as unresolved comments)
		await expect(checklistItem, "Checklist item comment link is updated upon comment resolution").toContainText("2 comments");
	});

	test.afterEach(async ({ page }) => {
		await deleteProject(page, projectId);
	});
});
