import { Page } from "@playwright/test"
import TestApiClient from "./test-api-client";

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
	const api = new TestApiClient(page);
	const project = await api.post("/api/projects", {
		name,
		url
	});

	await page.goto(`https://localhost/project/${project.id}/`);

	return project.id;
};

/**
 * Deletes the project with the provided ID.
 */
const deleteProject = async (page: Page, projectId: string) => {
	const api = new TestApiClient(page);
	await api.delete(`/api/projects/${projectId}`);
};

export { login, createProject, deleteProject };
