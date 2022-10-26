import { test, expect } from "@playwright/test";
import { login, createProject, deleteProject } from "../utilities";

const firstItemText = "Every page loads correctly without any error.";
let projectId;

test.describe("checklist", () => {
	test.beforeEach(async ({ page }) => {
		await login(page);
		projectId = await createProject(page);
	});

	test("basic checklist features", async ({ page }) => {
		// Open the checklist
		await page.click("#sidebar >> text=Checklist");

		// Wait for checklist items to load
		await page.waitForSelector(`checklist-item-list >> text=${firstItemText}`);

		// Get the first checklist item
		const firstItemGroup = page.locator("checklist-item-list").first();
		const firstGroupTitle = page.locator("h2").first();
		const progressionIndicator = firstGroupTitle.locator(".progression");
		const checklistItems = firstItemGroup.locator(".nb--list-item");
		const itemCount = await checklistItems.count();

		await checklistItems.first().locator("nb-checkbox").click({ timeout: 1000 });
		await expect(progressionIndicator.locator(".completed-count"), "Group title progress is updated to match checked item count").toContainText("1");
		await expect(progressionIndicator.locator(".total-count"), "Group title progress is updated to match checked item count").toContainText(itemCount.toString());

		for (let i = 1; i < itemCount; i++) {
			await checklistItems.nth(i).locator("nb-checkbox").click({ timeout: 1000 });
		}

		// Ensure completion indicator is shown
		await expect(firstGroupTitle.locator(".completion-indicator"), "Group is marked as completed once all items are checked").toBeVisible();

		// Ensure completed group is auto-closed
		await expect(checklistItems.locator(`checklist-item-list >> text=${firstItemText}`)).not.toBeVisible();
	});

	test.afterEach(async ({ page }) => {
		await deleteProject(page, projectId);
	});
});
