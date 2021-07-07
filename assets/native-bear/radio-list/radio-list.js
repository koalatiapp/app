import { LitElement, html, css } from "lit";
import stylesReset from "../styles-reset.js";

export class NbRadioList extends LitElement {
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

				fieldset { padding: 0; margin: 0; border: none; }
				legend { font-size: 1rem; font-weight: 500; color: var(--color-gray-darker); }
				.option-wrapper { margin-top: .5rem; }
				label { font-size: 1rem; font-weight: 400; color: var(--color-gray-darker); cursor: pointer; }
				input { display: inline-block; width: 1rem; height: 1rem; margin: 0.2rem .2rem 0 .2rem; vertical-align: top; background-color: var(--color-gray-lighter); box-shadow: 0 0 0px 1px var(--color-blue-80); border-radius: 50%; outline: none; -webkit-appearance: none; appearance: none; transition: box-shadow .15s ease-in-out; }
				input:hover { box-shadow: 0 0 0px 1px var(--color-blue-80), 0 2px 10px 0 rgba(var(--shadow-rgb), .15); }
				input:focus { box-shadow: 0 0 0px 2px var(--color-blue-80), 0 2px 10px 0 rgba(var(--shadow-rgb), .15); }
				input:checked { background-color: var(--color-blue-80); border: 3px solid var(--color-gray-lighter); }
				input:checked:hover + label { cursor: default; }
				input:not(:checked):hover + label { color: var(--color-blue-80); }

				@media (prefers-color-scheme: dark) {

				}
			`
		];
	}

	static get properties() {
		return {
			name: {type: String},
			value: {type: String|Number},
			required: {type: Boolean},
			readonly: {type: Boolean},
			disabled: {type: Boolean},
			label: {type: String},
			inputId: {type: String},
			options: {attribute: false},
		};
	}

	constructor()
	{
		super();
		this.internals = this.attachInternals();
		this.inputId = "radio" + Math.random().toString(16).slice(2);
		this.options = [];
		this.label = "";
		this.name = this.inputId;
		this.value = "";
		this._required = false;
		this.readonly = false;
		this.disabled = false;
	}

	connectedCallback()
	{
		super.connectedCallback();

		// Generate options from child <option> elements if there are some
		const optionNodes = this.querySelectorAll("option");
		if (optionNodes.length) {
			const options = [];

			for (const option of optionNodes) {
				options.push({ value: option.value, label: option.textContent });

				if (option.selected) {
					this.value = option.value;
				}
			}

			this.options = options;
		}
	}

	render()
	{
		return html`
			<fieldset @change=${this._updateValue}>
				${this.label ? html`<legend>${this.label}</legend>` : ""}
				${this.options.map(({ label, value }) => {
					const optionId = this.inputId + "-" + this._hashOption(value);

					return html`
						<div class="option-wrapper">
							<input type="radio" name=${this.name} id=${optionId} value=${value} ?required=${this._required} ?checked=${this.value == value} ?readonly=${this.readonly} ?disabled=${this.disabled}>
							<label for=${optionId}>${label}</label>
						</div>
					`;
				})}
			</fieldset>
	  	`;
	}

	set required(isRequired)
	{
		this._required = isRequired;
		this.internals.ariaRequired = isRequired;
	}

	get input()
	{
		return this.shadowRoot.querySelector("input:checked") ?? this.shadowRoot.querySelector("input");
	}

	firstUpdated()
	{
		this._updateValue();
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

		if (this._required && !input.checked) {
			validity = { valueMissing: true, valid: false };
			validationMessage = typeof Translator == "undefined" ? "Please select an option." : Translator.trans("generic.form.value_missing");
		}

		if (!validity.valid && !validationMessage) {
			validationMessage = input.validationMessage;
		}

		const newValue = this.disabled || !input.checked ? null : input.value;
		this.value = newValue;
		this.internals.setValidity(validity, validationMessage, input);
		this.internals.setFormValue(newValue);
	}

	_hashOption(optionValue)
	{
		if (typeof optionValue != "string") {
			optionValue = optionValue?.toString() ?? "";
		}

		let hash = 0;

		for (let i = 0; i < optionValue.length; i++) {
			hash = ((hash << 5) - hash) + optionValue.charCodeAt(i);
			hash |= 0;
		}

		return hash;
	}
}

customElements.define("nb-radio-list", NbRadioList);
