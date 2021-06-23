import { LitElement, html, css } from "lit";
import stylesReset from "../styles-reset.js";

export class NbField extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: block; }
				.label { font-size: 1rem; font-weight: 500; color: var(--color-gray-dark); }
			`
		];
	}

	static get properties() {
		return {
			label: {type: String},
		};
	}

	constructor()
	{
		super();
		this.label = "";
	}

	render()
	{
		return html`
			<div class="label">${this.label}</div>
			<slot></slot>
	  	`;
	}
}

customElements.define("nb-field", NbField);
