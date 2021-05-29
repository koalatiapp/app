import { LitElement, html, css } from "lit";
import stylesReset from "../styles-reset.js";

let submitOnEnterRegistered = false;

export class NbButton extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: inline-block; }
				.button { display: flex; justify-content: center; align-items: center; padding: 16px 30px; font-family: inherit; font-size: .85rem; font-weight: 600; line-height: 1.3; text-decoration: none; text-align: center; color: var(--button-text-color, #fff); background-color: var(--button-bg-color, #2751e6); border: none; border-radius: 13px; box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb, "0, 0, 0"), .15); cursor: pointer; transition: background .25s ease, box-shadow .25s ease; }
				.button:hover { color: var(--button-text-color-hover, white); background-color: var(--button-bg-color-hover, #5074f2); box-shadow: 0 3px 15px 0 rgba(var(--shadow-rgb, "0, 0, 0"), .25); }
				.button.small { padding: 10px 20px; font-weight: 500; }
				.button.danger { --button-bg-color: var(--color-red); --button-bg-color-hover: var(--color-red-light); }
				.button.warning { --button-bg-color: var(--color-orange); --button-bg-color-hover: var(--color-orange-light); }
				.button.gray { --button-bg-color: #d9ddea; --button-bg-color-hover: var(--color-gray); color: var(--color-gray-dark); }
				.button.dark { --button-bg-color: var(--color-blue-dark); }

				@media (prefers-color-scheme: dark) {
					.button.gray { --button-bg-color: #393f56; --button-bg-color-hover: var(--color-gray-light); color: var(--color-gray); }
				}
			`
		];
	}

	static get properties() {
		return {
			href: {type: String, attribute: true},
			type: {type: String, attribute: true},
			size: {type: String, attribute: true},
			color: {type: String, attribute: true},
			target: {type: String, attribute: true},
			name: {type: String, attribute: true},
			loading: {type: Boolean, attribute: true, reflect: true},
			disabled: {type: Boolean, attribute: true, reflect: true},
			_classes: {state: true}
		};
	}

	constructor()
	{
		super();
		this.href = null;
		this.type = "button";
		this.size = "";
		this.color = "";
		this.target = "_self";
		this.name = "";
		this.loading = false;
		this.disabled = false;
		this.clickListener = null;
	}

	render()
	{
		if (this.href) {
			return html`
				<a href=${this.href} class=${this._classes.join(" ").trim()} target=${this.target} ?disabled=${this.disabled} rel="${this.target == "_blank" ? "noopener" : ""}">
					<slot></slot>
				<a>
			`;
		}

		return html`
			<button type=${this.type} class=${this._classes.join(" ").trim()} name=${this.name} ?disabled=${this.disabled} @click=${this._handleClick}>
				<slot></slot>
			</button>
	  	`;
	}

	connectedCallback()
	{
		super.connectedCallback();
		this._registerFormSubmitOnEnter();
	}

	get _classes()
	{
		return [
			"button",
			this.size,
			this.color,
			this.loading ? "loading" : "",
		];
	}

	_handleClick(e)
	{
		if (this.disabled || this.loading) {
			e.preventDefault();
			e.stopPropagation();
		}

		if (this.type == "submit") {
			const form = this.closest("form");

			if (form) {
				e.preventDefault();
				const tmpButton = document.createElement("button");
				tmpButton.type = "submit";
				tmpButton.name = this.name;
				tmpButton.target = this.target;
				tmpButton.style.display = "none";
				form.appendChild(tmpButton);
				tmpButton.click();
				tmpButton.remove();
			}
		}
	}

	/**
	 * Reimplements the default form submission on Enter feature,
	 * which is broken when using a submit button inside a web component.
	 */
	_registerFormSubmitOnEnter()
	{
		if (!submitOnEnterRegistered) {
			submitOnEnterRegistered = true;
			window.addEventListener("keydown", (e) => {
				const form = e.target.closest("form");

				if (!form || e.key != "Enter" || e.target.matches("textarea, nb-button")) {
					return;
				}

				e.preventDefault();

				const nbButton = form.querySelector("nb-button[type='submit']");
				const innerSubmitButton = nbButton?.shadowRoot.querySelector("button");
				innerSubmitButton?.click();
			});
		}
	}
}

customElements.define("nb-button", NbButton);
