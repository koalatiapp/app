import { Page } from "@playwright/test"

const login = async (page: Page, email: string = "name@email.com", password: string = "123456") => {
	await page.goto("https://localhost/");
	await page.fill("#input-email input", email);
	await page.fill("#input-password input", password);
	await Promise.all([page.waitForNavigation(), page.click("nb-button:has-text('Sign in')")]);
};

/**
 * Creates a new project with the given name and URL, and returns its ID.
 * After creation, the page will be on the project's dashboard.
 *
 * @returns {string} ID of the newly created project
 */
const createProject = async (page: Page, name: string = "Sample website", url: string = "https://sample.koalati.com") => {
	// Go to project creation page through quick actions
	await page.hover("#quick-actions .toggle");
	await page.click("text=Create a project");
	await page.waitForSelector("text=Give your project a name");

	// Fill in the project creation form
	await page.fill("text=Give your project a name", name);
	await page.fill("text=Enter your website's URL", url);
	await page.click("nb-button:has-text('Create project')");

	// Wait for confirmation message
	await page.waitForSelector("text=has been created successfully")
	await page.waitForURL(/^http.+\/project\/[a-zA-Z0-9]+\/.*/);

	const projectId = page.url().replace(/.+\/project\/([a-zA-Z0-9]+)\//, "$1");

	return projectId;
};

/**
 * Deletes the project with the provided ID.
 */
const deleteProject = async (page: Page, projectId) => {
	// Go to the project's settings in the deletion tab
	await page.goto(`https://localhost/project/${projectId}/settings#delete`);
	await page.waitForSelector("text=I am certain that I want to delete the project");

	// Delete the project
	await page.click("text=I am certain that I want to delete the project");
	await page.click("text=Delete this project");

	// Wait for confirmation message
	await page.waitForSelector("text=has been deleted successfully")
};

export { login, createProject, deleteProject };
