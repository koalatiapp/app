import { LitElement, html, css } from "lit";
import stylesReset from "../styles-reset.js";
import fontAwesomeImport from "../../utils/fontawesome-import.js";
import getActiveElement from "../../utils/get-active-element.js";
import elementContains from "../../utils/element-contains.js";
import { MousewheelPreventionController } from "../../utils/controller/mousewheel-prevention-controller.js";

/**
 * Keeps a reference to every active sidepanel.
 * @type {NbSidePanel[]}
 */
const sidepanelStack = [];

/**
  * Callback for the `keydown` event listener that handles closing the sidepanels via the Escape key.
  */
const escapeKeydownCallback = function(e) {
	if (e.code == "Escape") {
		NbSidePanel.closeCurrent();
	}
};

export class NbSidePanel extends LitElement {
	#isClosing = false;
	#originalFocusElement = null;

	static get styles()
	{
		return css`
			${stylesReset}
			:host { display: flex; width: 100%; max-width: 500px; flex-direction: column; background-color: var(--color-white); box-shadow: 0 0 3rem rgba(var(--shadow-rgb), .25); position: fixed; top: 0; right: 0; bottom: 0; }

			header { display: flex; justify-content: space-between; align-content: flex-start; gap: 1.5rem; padding: 1.5rem; background-color: var(--color-white); border-bottom: 1px solid var(--color-gray-light); }

			h2 { margin-bottom: 0; }
			.context { font-size: .75rem; color: var(--color-gray-dark); }
			.context:empty { display: none; }

			.content { padding: 1.5rem; overflow: auto; }

			@media (prefers-color-scheme: dark) {

			}
		`;
	}

	static get properties() {
		return {
			title: {type: String},
			context: {type: String},
		};
	}

	constructor()
	{
		super();
		this.title = "";
		this.context = "";
		new MousewheelPreventionController(this, () => this.shadowRoot.querySelector(".content"));
	}

	connectedCallback()
	{
		super.connectedCallback();

		this.setAttribute("role", "complementary");
		this.setAttribute("aria-labelledby", "sidepanel-title");
		this.animateAppearance()
			.then(() => {
				this.#initCloseEventListeners();
				sidepanelStack.push(this);
			});
	}

	firstUpdated()
	{
		this.#originalFocusElement = getActiveElement();
		this.shadowRoot.querySelector("#sidepanel").focus();
	}

	render()
	{
		return html`
			${fontAwesomeImport}
			<div id="sidepanel" tabindex="-1"></div>
			<header>
				<div class="heading">
					<h2 id="sidepanel-title">
						<nb-markdown barebones>
							<script type="text/markdown">${this.title}</script>
						</nb-markdown>
					</h2>
					<div class="context">${this.context}</div>
				</div>
				<div class="actions">
					<nb-icon-button size="small" color="gray" @click=${this.close}>
						<i class="far fa-times"></i>
					</nb-icon-button>
				</div>
			</header>
			<div class="content">
				<slot></slot>
			</div>
	  	`;
	}

	close()
	{
		if (this.#isClosing) {
			return;
		}

		this.#isClosing = true;
		this.dispatchEvent(new CustomEvent("close"));
		this.setAttribute("aria-hidden", true);
		this.animateDisappearance().then(() => {
			this.remove();
			this.dispatchEvent(new CustomEvent("closed"));
		});

		sidepanelStack.pop();

		this.#originalFocusElement?.focus?.();

		// Remove the Escape listener if there is no more modal
		if (!sidepanelStack.length) {
			NbSidePanel.#removeEscapeEventListener();
		}
	}

	animateAppearance()
	{
		return new Promise(resolve => {
			const animation = this.animate(
				[
					{ transform: "translateX(500px)" },
					{ transform: "translateX(0px)" },
				],
				{
					duration: 350,
					easing: "ease-out",
					iterations: 1
				}
			);

			animation.onfinish = () => {
				resolve();
			};
		});
	}

	animateDisappearance()
	{
		return new Promise(resolve => {
			const animation = this.animate(
				[
					{ transform: "translateX(0px)" },
					{ transform: "translateX(500px)" },
				],
				{
					duration: 350,
					easing: "ease-out",
					iterations: 1
				}
			);

			animation.onfinish = () => resolve();
		});
	}

	/**
	 * Initializes the event listeners related to the modal, such as the close button.
	 */
	#initCloseEventListeners()
	{
		// Initialize document-wide event listener if this is the first modal of the stack
		if (!sidepanelStack.length) {
			window.addEventListener("keydown", escapeKeydownCallback);
		}

		const checkForOutsideClick = (e) => {
			const exceptedSelector = ".flash-message";

			// Also allow children of all excepted selectors
			const expandedExceptedSelector = exceptedSelector.split(", ").map(selector => `${selector}, ${selector} *`).join(", ");

			if (e.isTrusted && !e.target.matches(expandedExceptedSelector) && !elementContains(this, e.target)) {
				window.removeEventListener("click", checkForOutsideClick);
				this.close();
			}
		};

		window.addEventListener("click", checkForOutsideClick);
	}

	/**
	 * Returns the currently active modal instance.
	 *
	 * @returns {Modal|null} Instance of the currently active modal (or null if none is active)
	 */
	static getCurrent()
	{
		return sidepanelStack[sidepanelStack.length - 1] ?? null;
	}

	/**
	  * Closes the currently active modal
	  */
	static closeCurrent()
	{
		NbSidePanel.getCurrent()?.close();
	}

	/**
	 * Removes the Esc key event listener, which triggers the Close function.
	 */
	static #removeEscapeEventListener()
	{
		if (NbSidePanel.getCurrent()) {
			return;
		}

		window.removeEventListener("keydown", escapeKeydownCallback);
	}
}

customElements.define("nb-sidepanel", NbSidePanel);
