import { LitElement, html, css } from "lit";
import stylesReset from "../styles-reset.js";

export class NbSelect extends LitElement {
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
				.input { display: block; width: 100%; max-width: 100%; padding: 8px 15px; margin: 6px 0; font-family: inherit; font-size: .95rem; font-weight: 400; line-height: 1.5rem; color: var(--color-gray-darker); background-color: var(--color-white); border: 2px solid var(--color-gray-light);; border-radius: 8px; outline: none; box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb), .025); box-sizing: border-box; transition: border-color .25s ease, box-shadow .25s ease; }
				.input:hover { box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb), .15); }
				.input:focus { border-color: var(--color-blue); box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb), .15); }
				.input::placeholder { color: var(--color-gray); }

				label { font-size: 1rem; font-weight: 500; color: var(--color-gray-darker); }

				/* Sizes */
				:host(.tiny) { width: 8ch; }
				:host(.small) { width: 25ch; }
				:host(.medium) { width: 40ch; }

				@media (prefers-color-scheme: dark) {
					.input { color: var(--color-black); background-color: var(--color-gray-lighter); border-color: #444867; }
				}
			`
		];
	}

	static get properties() {
		return {
			name: {type: String},
			options: {attribute: false},
			value: {type: String|Number},
			required: {type: Boolean},
			readonly: {type: Boolean},
			disabled: {type: Boolean},
			label: {type: String},
			inputId: {type: String},
		};
	}

	constructor()
	{
		super();
		this.internals = this.attachInternals();
		this.label = "";
		this.name = "";
		this.value = "";
		this._required = false;
		this.readonly = false;
		this.disabled = false;
		this.options = [];
		this.inputId = "input" + Math.random().toString(16).slice(2);
	}

	render()
	{
		return html`
			${this.label ? html`<label for=${this.inputId}>${this.label}</label>` : ""}
			<select class="input" id=${this.inputId} name=${this.name} ?readonly=${this.readonly} ?disabled=${this.disabled} @input=${this._updateValue}>
				${Object.keys(this.options).map(value => html`
					<option value=${value} ?selected=${this.value == value}>${this.options[value]}</option>
				`)}
			</select>
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
			validity = { valueMissing: true, valid: false };
			validationMessage = typeof Translator == "undefined" ? "Please fill out this field." : Translator.trans("generic.form.value_missing");
		}

		if (!validity.valid && !validationMessage) {
			validationMessage = input.validationMessage;
		}

		const newValue = this.disabled ? null : input.value;
		this.value = newValue;
		this.internals.setValidity(validity, validationMessage, input);
		this.internals.setFormValue(newValue);
	}
}

customElements.define("nb-select", NbSelect);
