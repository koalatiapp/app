import { LitElement, html, css } from "lit";
import stylesReset from "../styles-reset.js";

export default class NbInput extends LitElement {
	static get formAssociated()
	{
		return true;
	}

	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: block; }
				.input { display: block; width: 100%; max-width: 100%; padding: 8px 15px; margin: 6px 0; font-family: inherit; font-size: 1rem; font-weight: 500; line-height: 1.5rem; color: var(--color-gray-dark); background-color: var(--color-white); border: 2px solid var(--color-gray-light); border-radius: 8px; outline: none; box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb), .025); box-sizing: border-box; -webkit-font-smoothing: antialiased; transition: border-color .25s ease, box-shadow .25s ease; }
				.input:hover { box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb), .15); }
				.input:focus { border-color: var(--color-blue); box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb), .15); }
				.input::placeholder { color: var(--color-gray); }

				label { font-size: 1rem; font-weight: 600; color: var(--color-gray-dark); }

				/* Sizes */
				:host(.tiny) { width: 8ch; }
				:host(.small) { width: 25ch; }
				:host(.medium) { width: 40ch; }
				:host([type="date"]) { width: 12ch; }

				@media (prefers-color-scheme: dark) {
					.input { color: var(--color-black); }
				}
			`
		];
	}

	static get properties() {
		return {
			name: {type: String, attribute: true},
			type: {type: String, attribute: true},
			placeholder: {type: String, attribute: true},
			value: {type: String|Number, attribute: true},
			step: {type: Number, attribute: true},
			required: {type: Boolean, attribute: true, reflect: true},
			readonly: {type: Boolean, attribute: true, reflect: true},
			disabled: {type: Boolean, attribute: true, reflect: true},
			label: {type: String, attribute: true},
			inputId: {type: String, attribute: true},
		};
	}

	constructor()
	{
		super();
		this.internals = this.attachInternals();
		this.label = "";
		this.name = "";
		this.value = "";
		this.type = "text";
		this.placeholder = "";
		this._required = false;
		this.readonly = false;
		this.disabled = false;
		this.inputId = "input" + Math.random().toString(16).slice(2);
	}

	render()
	{
		return html`
			${this.label ? html`<label for=${this.inputId}>${this.label}</label>` : ""}
			<input class="input" id=${this.inputId} type=${this.type} placeholder=${this.placeholder} value=${this.value}  ?readonly=${this.readonly} ?disabled=${this.disabled} @input=${this._updateValue}>
	  	`;
	}

	set required(isRequired)
	{
		this._required = isRequired;
		this.internals.ariaRequired = isRequired;
	}

	get input()
	{
		return this.shadowRoot.querySelector(".input");
	}

	firstUpdated()
	{
		this._updateValue();

		if (this.autofocus && document.activeElement == document.body)  {
			this.focus();
		}
	}

	focus()
	{
		this.input.focus();
	}

	_updateValue()
	{
		const input = this.input;
		let validity = input.validity;
		let validationMessage = "";

		if (this._required && !input.value.toString().length) {
			validity = { valueMissing: true };
			validationMessage = typeof Translator == "undefined" ? "Please fill out this field." : Translator.trans("generic.form.value_missing");
		}

		this.value = input.value;
		this.internals.setValidity(validity, validationMessage, input);
		this.internals.setFormValue(input.value);
	}
}

customElements.define("nb-input", NbInput);
