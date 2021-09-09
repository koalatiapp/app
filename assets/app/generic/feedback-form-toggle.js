import { LitElement, html, css } from "lit";
import Modal from "../../utils/modal.js";
import { ApiClient } from "../../utils/api";

export class FeedbackFormToggle extends LitElement {
	static get styles()
	{
		return css`
			:host { display: inline-block; }
		`;
	}

	connectedCallback()
	{
		super.connectedCallback();

		this.addEventListener("click", (e) => {
			e.preventDefault();
			this.showFeedbackForm();
		});
	}

	render()
	{
		return html`
			<slot></slot>
	  	`;
	}

	showFeedbackForm()
	{
		new Modal({
			title: "Help us improve Koalati",
			content: html`
				<form @submit=${this.constructor._submitCallback}>
					<nb-radio-list name="type" label="Type of feedback" required>
						<option value="bug" selected>Bug report</option>
						<option value="suggestion">Suggestion</option>
						<option value="other">Other</option>
					</nb-radio-list>

					<hr class="spacer small">

					<nb-input name="message" type="textarea" label="What would you like to tell us?" rows="6" required></nb-input>

					<hr>

					<div class="button-container center">
						<nb-button type="submit">Send feedback</nb-button>
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

		ApiClient.post("api_feedback_submit", data, null).then(() => {
			window.Flash.show("success", Translator.trans("feedback.flash.success"));
			Modal.closeCurrent();
		}).catch(() => {
			window.Flash.show("danger", Translator.trans("feedback.flash.error"));
			submitButton.loading = false;
		});
	}
}

customElements.define("feedback-form-toggle", FeedbackFormToggle);
