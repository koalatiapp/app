import { LitElement, html, css } from "lit";
import stylesReset from "../styles-reset.js";
import fontawesomeImport from "../../utils/fontawesome-import";

export class NbDropdown extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: block; position: relative; }

				.dropdown-list { display: block; padding: 0; margin: 0; list-style: none; white-space: nowrap; background-color: var(--color-white); border-radius: 10px; box-shadow: 0 3px 15px rgba(var(--shadow-rgb), .2); opacity: 0; position: absolute; z-index: 1; pointer-events: none; transition: opacity .25s ease; }
				.dropdown-list li:not(:first-child) { border-top: 1px solid var(--color-gray-light); }
				.dropdown-list a { display: block; width: 100%; padding: 10px 15px; transition: background .15s ease; }
				.dropdown-list a:hover { background-color: var(--color-gray-light); }
				.dropdown-list > li:first-child button { border-radius: 10px 10px 0 0; }
				.dropdown-list > li:last-child button { border-radius: 0 0 10px 10px; }

				.dropdown-button { display: block; width: 100%; height: 100%; padding: 10px 15px; margin: 0; text-align: left; color: inherit; background-color: transparent; border: none; appearance: none; cursor: pointer; }
				.dropdown-button:hover { background-color: var(--color-gray-light); }

				:host([open]),
				.floating-dropdown:focus-within,
				:host([reveal-on-hover]:hover) .dropdown-list { opacity: 1; pointer-events: auto; }

				@media (prefers-color-scheme: dark) {

				}
			`
		];
	}

	static get properties() {
		return {
			open: {type: Boolean},
			color: {type: String},
			eventData: {attribute: false},
			options: {attribute: false}
		};
	}

	constructor()
	{
		super();
		this.open = false;
		this.eventData = {};
		this.options = [];
		this.color = "gray";
	}

	render()
	{
		return html`
			${fontawesomeImport}

			<nb-button size="small" color=${this.color} no-shadow>
				<slot name="toggle"></slot>
				&nbsp;&nbsp;&nbsp;
				<i class="far fa-angle-down"></i>
			</nb-button>

			<ul class="dropdown-list">
				${Object.keys(this.options).map(key => html`
					<li>
						<button type="button" class="dropdown-button" @click=${() => this.dispatchEvent(new CustomEvent("select", { detail: Object.assign({ value: key, label: this.options[key], dropdown: this }, this.eventData) }))}>
							${this.options[key]}
						</button>
					</li>
				`)}
			</ul>
	  	`;
	}

	connectedCallback()
	{
		super.connectedCallback();

		this.addEventListener("select", e => {
			this.slottedToggleElement.innerHTML = e.detail.label;
		});
	}

	get slottedToggleElement()
	{
		return this.shadowRoot.querySelector("slot[name='toggle']").assignedElements()[0];
	}
}

customElements.define("nb-dropdown", NbDropdown);
