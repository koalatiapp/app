/**
 * Keeps a reference to every active modal.
 * This is used to close all modals at once.
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
		this.options = this.__standardizeOptions(options);
		this.wrapperElement = this._createWrapper();
		this.dialogElement = this._createDialog();
		this._initEventListeners();
		this._loadContent();

		modalStack.push(this);
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
		if (typeof options.content != "string") {
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

		return wrapper;
	}

	/**
	 * Generates the dialog element that represents the modal.
	 * Any content that can be rendered synchronously is rendered at this stage.
	 */
	_createDialog()
	{
		const dialog = document.createElement("div");
		dialog.setAttribute("role", "dialog");
		dialog.setAttribute("aria-labelledby", `modal-title-${this.guid}`);
		dialog.setAttribute("aria-describedby", `modal-content-${this.guid}`);
		dialog.innerHTML = `
			<header>
				<div class="modal-title" id="modal-title-${this.guid}">${this.options?.title}</div>
				<div class="modal-actions">
					<button type="button" class="modal-close" aria-label="Close this dialog">
						<i class="far fa-times" aria-hidden="true"></i>
					</button>
				</div>
			</header>
			<div class="modal-content" id="modal-content-${this.guid}">${this.options.content instanceof Promise ? "" : this.options?.content}</div>
		`;
		this.wrapperElement.appendChild(dialog);

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

		const contentElement = this.dialogElement.querySelector(".modal-content");

		if (this.options?.content) {
			this.options.content.then(content => contentElement.innerHTML = content);
		} else {
			fetch(this.options?.contentUrl)
				.then(response => response.text())
				.then(content => contentElement.innerHTML = content);
		}
	}

	/**
	 * Initializes the event listeners related to the modal, such as the close button.
	 */
	_initEventListeners()
	{
		this.dialogElement.querySelector("header > button.modal-close").addEventListener("click", (e) => {
			e.preventDefault();
			this.close();
		});
	}

	/**
	 * Opens the modal and focuses its first focusable element.
	 * If another modal was present before, it will be disabled.
	 */
	show()
	{
		// @TODO: Open the modal
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

		// @TODO: Close the modal
		// @TODO: Show previous modal (if any)

		// Restore focus
		this.originalFocus?.focus();

		// @TODO: Return a promise with a boolean indicating if the modal is closed or not
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
}
