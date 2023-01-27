import { LitElement, html, css } from "lit";
import Modal from "../../utils/modal.js";
import { InternalApiClient } from "../../utils/internal-api";

export class FeedbackFormToggle extends LitElement {
	static get styles()
	{
		return css`
			:host { display: inline-block; }
		`;
	}

	static get properties() {
		return {
			type: {type: String},
			displayMode: {type: String},
			defaultMessage: {type: String},
		};
	}

	constructor()
	{
		super();

		this.setAttribute("role", "button");

		this.type = null;
		this.displayMode = null;
		this.defaultMessage = null;
	}

	connectedCallback()
	{
		super.connectedCallback();
		this._becomeClickable();
	}

	_becomeClickable()
	{
		if (this.displayMode == "button") {
			return;
		}

		this.addEventListener("click", (e) => {
			e.preventDefault();
			this.showFeedbackForm();
		});

		this.setAttribute("tabindex", 0);

		this.addEventListener("keydown", (e) => {
			if (e.key != "Enter") {
				return;
			}

			e.preventDefault();
			this.click();
		});
	}

	render()
	{
		if (this.displayMode == "button") {
			return html`
				<nb-button @click=${() => this.showFeedbackForm()} size=${this.getAttribute("size") || "small"} color=${this.getAttribute("color") || "gray"}>
					<slot></slot>
				</nb-button>
			`;
		}

		return html`
			<slot></slot>
	  	`;
	}

	showFeedbackForm()
	{
		new Modal({
			title: Translator.trans("feedback.form.title"),
			content: html`
				<form @submit=${this.constructor._submitCallback}>
					<nb-radio-list name="type" label="${Translator.trans("feedback.form.type.label")}" required>
						<option value="bug" ?selected=${!this.type || this.type == "bug"}>${Translator.trans("feedback.form.type.bug")}</option>
						<option value="suggestion" ?selected=${this.type == "suggestion"}>${Translator.trans("feedback.form.type.suggestion")}</option>
						<option value="other" ?selected=${this.type == "other"}>${Translator.trans("feedback.form.type.other")}</option>
					</nb-radio-list>

					<hr class="spacer small">

					<nb-input name="message" type="textarea" label="${Translator.trans("feedback.form.message.label")}" value=${this.defaultMessage} rows="6" required></nb-input>

					<hr>

					<div class="button-container center">
						<nb-button type="submit">${Translator.trans("feedback.form.submit")}</nb-button>
					</div>
				</form>
			`
		});
	}

	static _submitCallback(e)
	{
		e.preventDefault();

		const form = e.target;
		const submitButton = form.querySelector("nb-button");
		const data = new FormData(form);
		data.append("url", window.location.href);

		submitButton.loading = true;

		InternalApiClient.post("api_feedback_submit", data, null).then(() => {
			window.Flash.show("success", Translator.trans("feedback.flash.success"));
			Modal.closeCurrent();

			window.plausible("Submitted feedback");
		}).catch(() => {
			window.Flash.show("danger", Translator.trans("feedback.flash.error"));
			submitButton.loading = false;
		});
	}
}

customElements.define("feedback-form-toggle", FeedbackFormToggle);
