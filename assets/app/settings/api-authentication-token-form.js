import { html, css, LitElement } from "lit";

export class ApiAuthenticationTokenForm extends LitElement {
	static get styles()
	{
		return css`
			nb-input { margin-bottom: 1rem; }
			nb-input:placeholder-shown { display: none; }
			nb-input::part(input) { word-break: break-all; }
		`;
	}

	static get properties()
	{
		return {
			response: {type: Object},
		};
	}

	constructor()
	{
		super();
		this.response = null;
	}

	render()
	{
		return html`
			${this.response ? html`
				<nb-input type="textarea" label="Authentication Token" name="token" value=${this.response.token} readonly></nb-input>
				<nb-input label="Refresh Token" name="refresh_token" value=${this.response.refresh_token} readonly></nb-input>
			` : ""}
			<nb-button @click=${this.#fetch}>${window.Translator.trans("user_settings.api.authentication.generate")}</nb-button>
		`;
	}

	#fetch()
	{
		const button = this.shadowRoot.querySelector("nb-button");
		button.toggleAttribute("loading", true);

		fetch("/internal-api/session-authentication", { headers: { "X-Requested-With": "XMLHttpRequest" }})
			.then(response => response.json())
			.then(response => {
				this.response = response;
				window.Flash.show("success", Translator.trans("user_settings.api.authentication.tokens_generated"));
			}).finally(() => {
				button.toggleAttribute("loading", false);
			});
	}
}

customElements.define("api-authentication-token-form", ApiAuthenticationTokenForm);
