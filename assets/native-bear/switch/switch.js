import { LitElement, html, css } from "lit";
import stylesReset from "../styles-reset.js";

export class NbSwitch extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: inline-flex; align-items: center; gap: .5rem; }
				.switch { display: inline-flex; align-items: center; width: 3rem; height: 1.6rem; padding: .2rem; background-color: var(--color-gray-light); border: 2px solid var(--color-gray-light); border-radius: 1.5rem; position: relative; cursor: pointer; -webkit-appearance: none; appearance: none; transition: background-color .4s ease, border-color .4s ease; }
				.ball { display: block; height: 100%; aspect-ratio: 1; background-color: var(--color-gray-dark); border-radius: 50%; position: relative; left: 0; transition: left .35s ease; }
				.state-label { font-size: .8rem; color: var(--color-gray-dark); }

				:host([checked]) .switch { background-color: var(--color-blue-80); border-color: var(--color-blue-80); }
				:host([checked]) .ball { background-color: var(--color-white); left: 1.5rem; }

				:host([labelFirst]) .switch { order: 2; }

				:host([showBothLabels]) { display: inline-grid; grid-template-columns: 1fr auto 1fr; }
				:host([showBothLabels]) .state-label.off { text-align: right; font-weight: 500; color: var(--color-black); }
				:host([showBothLabels]) .state-label.on { text-align: left; }
				:host([showBothLabels][checked]) .state-label.on { font-weight: 500; color: var(--color-black); }
				:host([showBothLabels][checked]) .state-label.off { font-weight: 400; color: var(--color-gray-dark); }

				@media (prefers-color-scheme: dark) {
					:host([checked]) .switch { background-color: var(--color-blue); border-color: var(--color-blue); }
					:host([checked]) .ball { background-color: var(--color-black); }
				}

				/* Safari support */
				@supports not (aspect-ratio: 1) {
					.ball { width: calc(1.2rem - 4px); }
				  }
			`
		];
	}

	static get properties()
	{
		return {
			onLabel: { type: String },
			offLabel: { type: String },
			checked: { type: Boolean, reflect: true },
			showBothLabels: { type: Boolean },
		};
	}

	constructor()
	{
		super();
		this.onLabel = null;
		this.offLabel = null;
		this.checked = false;
		this.showBothLabels = false;
		this.internals = this.attachInternals();

		try {
			this.internals.role = "switch";
		} catch (_) {
			// Looks like you're on Safari. Though luck!
		}
		this.internals.ariaChecked = this.checked ? "true" : "false";
		this.setAttribute("role", "switch");
	}

	render()
	{
		if (this.showBothLabels) {
			return html`
				<span class="state-label off">${this.offLabel}</span>
				<span class="switch">
					<span class="ball"></span>
				</span>
				<span class="state-label on">${this.onLabel}</span>
			  `;
		}

		return html`
			<span class="switch">
				<span class="ball"></span>
			</span>
			${this.onLabel && this.offLabel ? html`<span class="state-label">${this.checked ? this.onLabel : this.offLabel}</span>` : ""}
	  	`;
	}

	connectedCallback()
	{
		super.connectedCallback();
		this._registerClickListener();
		this.internals.ariaChecked = this.checked ? "true" : "false";

		this.setAttribute("tabindex", 0);

		this.addEventListener("keydown", (e) => {
			if (e.key != "Enter") {
				return;
			}

			e.preventDefault();
			this.click();
		});
	}

	toggle(forceState)
	{
		const newState = forceState ?? !this.checked;

		if (newState == this.checked) {
			return;
		}

		this.checked = newState;
		this.internals.ariaChecked = this.checked ? "true" : "false";

		this.dispatchEvent(new Event("change"));
		this.dispatchEvent(new CustomEvent(this.checked ? "on" : "off"));
	}

	_registerClickListener()
	{
		this.addEventListener("click", e => {
			e.preventDefault();
			this.toggle();
		});
	}
}

customElements.define("nb-switch", NbSwitch);
