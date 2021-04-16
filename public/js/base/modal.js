/**
 * Keeps a reference to every active modal.
 * This is used to close all modals at once.
 * @type {Modal[]}
 */
const modalStack = [];

/**
 * A modal window that displays content in a standalone dialog box over the rest of the page.
 */
export default class Modal {
	/**
	 *
	 * @param {object} [options={}] - Options for the modal's initialization
	 * @param {string} [options.title] - Content to display as the title of the modal.
	 * @param {string|Promise<string>} [options.content] - Content to display inside the modal.
	 * @param {string} [options.contentUrl] - A URL from which to fetch the content to display inside the modal.
	 * @param {boolean} [options.confirmClose=false] - Whether the user should have to confirm when they want to close the modal.
	 */
	constructor(options = {})
	{
		if (!options.content && !options.contentUrl) {
			throw new Error("You must provide either the `content` or the `contentUrl` option when creating a modal.");
		}

		this.originalFocus = document.activeElement;
		this.guid = this._generateGuid();
		this.options = this._standardizeOptions(options);
		this.wrapperElement = this._createWrapper();
		this.dialogElement = this._createDialog();
		this.contentElement = this.dialogElement.querySelector(".modal-content");
		this._initEventListeners();
		this._loadContent();

		modalStack.push(this);

		setTimeout(() => { this.show(); }, 10);
	}

	/**
	 *
	 * @param {object} [options={}] - Options for the modal's initialization
	 * @param {string} [options.title] - Content to display as the title of the modal.
	 * @param {string|Promise<string>} [options.content] - Content to display inside the modal.
	 * @param {string} [options.contentUrl] - A URL from which to fetch the content to display inside the modal.
	 * @param {boolean} [options.confirmClose=false] - Whether the user should have to confirm when they want to close the modal.
	 */
	_standardizeOptions(options)
	{
		if (["undefined", "string"].indexOf(typeof options.content) == -1) {
			options.content = Promise.resolve(options.content);
		}

		return Object.freeze(options);
	}

	/**
	 * Generates a random GUID that can be used as a unique ID for the modal.
	 */
	_generateGuid()
	{
		return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function(c) {
			var r = Math.random() * 16 | 0, v = c == "x" ? r : (r & 0x3 | 0x8);
			return v.toString(16);
		});
	}

	/**
	 * Generates the modal's wrapper element (and the backdrop element).
	 */
	_createWrapper()
	{
		const wrapper = document.createElement("div");
		wrapper.className = "modal-wrapper";

		const backdrop = document.createElement("div");
		backdrop.className = "modal-backdrop";

		wrapper.appendChild(backdrop);
		document.body.appendChild(wrapper);

		return wrapper;
	}

	/**
	 * Generates the dialog element that represents the modal.
	 * Any content that can be rendered synchronously is rendered at this stage.
	 */
	_createDialog()
	{
		const dialog = document.createElement("div");
		dialog.className = "modal";
		dialog.setAttribute("role", "dialog");
		dialog.setAttribute("aria-labelledby", `modal-title-${this.guid}`);
		dialog.setAttribute("aria-describedby", `modal-content-${this.guid}`);
		dialog.setAttribute("aria-hidden", "true");
		dialog.innerHTML = `
			<header class="modal-header">
				<div class="modal-title" id="modal-title-${this.guid}">${this.options.title ?? ""}</div>
				<div class="modal-actions">
					<button type="button" class="modal-close small icon-only" aria-label="Close this dialog">
						<i class="far fa-times" aria-hidden="true"></i>
					</button>
				</div>
			</header>
			<div class="modal-content" id="modal-content-${this.guid}">${this.options.content instanceof Promise ? "" : (this.options?.content ?? "")}</div>
		`;
		this.wrapperElement.prepend(dialog);

		return dialog;
	}

	/**
	 * Loads the modal's content from the `contentUrl` or `content` promise provided in the options.
	 */
	_loadContent()
	{
		if (!this.options?.contentUrl && !(this.options?.content instanceof Promise)) {
			return;
		}

		this.toggleLoading(true);

		const contentElement = this.dialogElement.querySelector(".modal-content");
		let contentPromise;

		if (this.options?.content) {
			// Use the content promise
			contentPromise = this.options.content;
		} else {
			// Fetch the content from the provided URL
			contentPromise = fetch(this.options?.contentUrl).then(response => response.text());
		}

		contentPromise.then(content => {
			contentElement.innerHTML = content;
		}).catch(() => {
			contentElement.innerHTML = "Sorry, an error occured while loading this content. Please try again.";
		}).finally(() => {
			this.toggleLoading(false);
		});
	}

	/**
	 * Initializes the event listeners related to the modal, such as the close button.
	 */
	_initEventListeners()
	{
		this.dialogElement.querySelector("button.modal-close").addEventListener("click", (e) => {
			e.preventDefault();
			this.close();
		});

		// @TODO: Add tab-targeting trap to prevent focus outside of modal
	}

	/**
	 * Opens the modal and focuses its first focusable element.
	 * If another modal was present before, it will be disabled.
	 */
	show()
	{
		// @TODO: Open the modal
		this.dialogElement.setAttribute("aria-hidden", "false");

		// @TODO: Focus the first focusable element in the modal
	}

	/**
	 * Closes the modal.
	 * If another modal was present underneath, it will be brought back up.
	 * The focus will be restored to the element that had it when the modal was created.
	 *
	 * @param {boolean} [skipConfirm=false] - Whether confirmation should be skipped if the modal requires confirmation on-close.
	 * @returns {Promise<boolean>} Returns a `Promise<boolean>` indicating if the modal was closed (`true`) or not (`false`).
	 */
	close(skipConfirm = false)
	{
		if (!skipConfirm) {
			// @TODO: Check for confirmation before closing
		}

		// Close the modal
		this.dialogElement.setAttribute("aria-hidden", "true");
		modalStack.pop();

		// Restore focus
		this.originalFocus?.focus();

		// Remove the nodes from the document
		setTimeout(() => { this.wrapperElement.remove(); }, 1000);

		// @TODO: Return a promise with a boolean indicating if the modal is closed or not
	}

	/**
	 * Toggles the loading state for the modal's content.
	 *
	 * @param {boolean} [forcedState] - Forces a specific state (`true“ enables the loading, `false` disables it)
	 */
	toggleLoading(forcedState)
	{
		const isLoading = forcedState ?? this.contentElement.getAttribute("aria-busy") != "true";

		if (isLoading) {
			this.contentElement.setAttribute("aria-live", "polite");
		} else {
			this.contentElement.removeAttribute("aria-live");
		}

		this.contentElement.setAttribute("aria-busy", isLoading ? "true" : "false");
	}

	/**
	 * Closes every active modal.
	 *
	 * @param {boolean} [skipConfirm=false] - Whether confirmations should be skipped when closing modals that require confirmation on-close.
	 * @returns {Promise<boolean>} Returns a `Promise<boolean>` indicating if the modal was closed (`true`) or not (`false`).
	 */
	static closeAll(skipConfirm = false)
	{
		// @TODO: Loop over every modal in the `modalStack`, calling and awaiting `close(skipConfirm)` for each of them
		// @TODO: Return a promise with a boolean indicating if the modal is closed or not
	}

	/**
	 * Returns the currently active modal instance.
	 *
	 * @returns {Modal|null} Instance of the currently active modal (or null if none is active)
	 */
	static getCurrent()
	{
		return modalStack[modalStack.length - 1] ?? null;
	}
}
