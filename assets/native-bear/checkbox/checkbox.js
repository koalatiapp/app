import { LitElement, html, css } from "lit";
import stylesReset from "../styles-reset.js";

export class NbCheckbox extends LitElement {
	static get formAssociated()
	{
		return true;
	}

	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: inline-flex; align-items: center; }

				input { display: inline-block; width: 1.375rem; height: 1.375rem; flex: 0 0 1.375rem; padding: 0; border: 2px solid var(--color-gray-light); border-radius: 3px; box-shadow: 0 1px 8px 0 rgba(var(--shadow-rgb), .05); appearance: none; cursor: pointer; }
				input:checked { background-color: var(--color-gray-light); background-image: url('/ext/fontawesome/svgs/regular/check.svg'); background-size: 90%; background-position: center; background-repeat: no-repeat; border-color: var(--color-blue-light); }
				input:hover { box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb), .15); }
				input:focus-visibles { border-color: var(--color-blue); box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb), .25); }

				label { margin-left: 5px; }

				@media (prefers-color-scheme: dark) {

				}
			`
		];
	}

	static get properties() {
		return {
			name: {type: String},
			value: {type: String|Number},
			checked: {type: Boolean},
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
		this.checked = false;
		this._required = false;
		this.readonly = false;
		this.disabled = false;
		this.inputId = "input" + Math.random().toString(16).slice(2);
	}

	render()
	{
		return html`
			<input type="checkbox" id=${this.inputId} name=${this.disableAutofill ? "" : this.name} value=${this.value} ?checked=${this.checked} ?readonly=${this.readonly} ?disabled=${this.disabled} @change=${this._updateValue}>
			${this.label ? html`<label for=${this.inputId}>${this.label}</label>` : ""}
	  	`;
	}

	set required(isRequired)
	{
		this._required = isRequired;
		this.internals.ariaRequired = isRequired;
	}

	get input()
	{
		return this.shadowRoot.querySelector("input");
	}

	firstUpdated()
	{
		this._updateValue(false);

		if (this.autofocus && document.activeElement == document.body)  {
			this.focus();
		}
	}

	focus()
	{
		this.input.focus();
	}

	click()
	{
		this.input.click();
	}

	_updateValue(triggerChange = true)
	{
		const input = this.input;
		let validity = input.validity;
		let validationMessage = "";
		this.checked = input.checked;

		if (this._required && !this.checked) {
			validity = { valueMissing: true, valid: false };
			validationMessage = typeof Translator == "undefined" ? "Please fill out this field." : Translator.trans("generic.form.value_missing");
		}

		if (!validity.valid && !validationMessage) {
			validationMessage = input.validationMessage;
		}

		this.internals.setValidity(validity, validationMessage, input);
		this.internals.setFormValue(this.disabled || !this.checked ? null : this.value);

		if (triggerChange) {
			this.dispatchEvent(new Event("change"));
		}
	}
}

customElements.define("nb-checkbox", NbCheckbox);
