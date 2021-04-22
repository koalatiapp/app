import { LitElement, html, css } from "lit";

export default class NbButton extends LitElement {
	static get styles()
	{
		return css`
			.button { display: flex; justify-content: center; align-items: center; padding: 16px 30px; font-family: inherit; font-size: 15px; font-weight: 700; text-align: center; color: var(--button-text-color, #fff); background-color: var(--button-bg-color, #2751e6); border: none; border-radius: 13px; box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb, "0, 0, 0"), .15); cursor: pointer; transition: background .25s ease, box-shadow .25s ease; }
			.button:hover { color: var(--button-text-color-hover, white); background-color: var(--button-bg-color-hover, #5074f2); box-shadow: 0 3px 15px 0 rgba(var(--shadow-rgb, "0, 0, 0"), .25); }
			.button.small { padding: 10px 20px; font-weight: 600; }
			.button.danger { --button-bg-color: var(--color-red); --button-bg-color-hover: var(--color-red-faded); }
			.button.warning { --button-bg-color: var(--color-orange); --button-bg-color-hover: var(--color-orange-faded); }
			.button.gray { --button-bg-color: #d9ddea; --button-bg-color-hover: var(--color-gray); color: var(--color-gray-dark); }
			.button.dark { --button-bg-color: var(--color-blue-dark); }
		`;
	}

	static get properties() {
		return {
			href: {type: String, attribute: true},
			type: {type: String, attribute: true},
			size: {type: String, attribute: true},
			color: {type: String, attribute: true},
			target: {type: String, attribute: true},
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
		this.loading = false;
		this.disabled = false;
		this.clickListener = null;
	}

	render()
	{
		if (this.href) {
			return html`
				<a href=${this.href} class="button ${this._classes.join(" ")}" target=${this.target} ?disabled=${this.disabled}>
					<slot></slot>
				<a>
			`;
		}

		return html`
			<button type=${this.type} class="button ${this._classes.join(" ")}" ?disabled=${this.disabled} @click=${this._handleClick}>
				<slot></slot>
			</button>
	  	`;
	}

	get _classes()
	{
		return [
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

		this.dispatchEvent(new Event("click"));

		if (this.type == "submit") {
			const form = this.closest("form");

			if (form) {
				e.preventDefault();
				const tmpButton = document.createElement("button");
				tmpButton.type = "submit";
				tmpButton.style.display = "none";
				form.appendChild(tmpButton);
				tmpButton.click();
				tmpButton.remove();
			}
		}
	}
}

customElements.define("nb-button", NbButton);
