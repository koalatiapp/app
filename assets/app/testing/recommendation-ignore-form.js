import { LitElement, html, css } from "lit";
import stylesReset from "../../native-bear/styles-reset.js";
import faImport from "../../utils/fontawesome-import";
import { ApiClient } from "../../utils/api";
import Modal from "../../utils/modal.js";

export class RecommendationIgnoreForm extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: block; }

				@media (prefers-color-scheme: dark) {

				}
			`
		];
	}

	static get properties() {
		return {
			ignoreType: { type: String },
			allowPageScope: { type: Boolean },
			ignoreScope: { type: String },
			recommendation: { attribute: false },
		};
	}

	constructor()
	{
		super();

		/**
		 * The recommendation or recommendation group to ignore.
		 *
		 * @type {object}
		 */
		this.recommendation = null;

		/**
		 * Whether the "page" scope option is allowed.
		 *
		 * This should only be turned on if the user requested to ignore
		 * a specific instance of a recommendation, where the page title
		 * or URL was displayed.
		 *
		 * @type {boolean}
		 * @default false
		 */
		this.allowPageScope = false;

		/**
		 * The scope option to select by default.
		 *
		 * @type {("page"|"project"|"user"|"organization")}
		 * @default "project"
		 */
		this.ignoreScope = "project";
	}

	render()
	{
		return html`
			${faImport}
			<form @submit="${this._submitCallback}">
				<nb-input type="hidden" name="recommendation_id" value=${this.recommendation.sampleId}></nb-input>
				<nb-field label=${Translator.trans("recommendation.ignore_form.recommendation")}>
					<nb-markdown barebones id="ignore-form-recommendation-title">
						<script type="text/markdown">${this.recommendation.title}</script>
					</nb-markdown>
				</nb-field>

				<hr class="spacer small">

				${this._renderScopeField()}

				<hr class="spacer small">

				<nb-button type="submit">
					${Translator.trans("recommendation.ignore_form.submit")}
				</nb-button>
			</form>
		`;
	}

	_renderScopeField()
	{
		const scopeOptions = [];

		if (this.allowPageScope) {
			scopeOptions.push("page");
		}

		scopeOptions.push("project");

		if (this.recommendation?.projectOwnerType == "organization") {
			scopeOptions.push("organization");
		} else if (this.recommendation?.projectOwnerType == "user") {
			scopeOptions.push("user");
		}

		if (scopeOptions.length > 1) {
			return html`
				<nb-radio-list name="scope" label=${Translator.trans("recommendation.ignore_form.scope.label")} value=${this.ignoreScope}>
					${scopeOptions.map(scope => html`<option value="${scope}">${Translator.trans("recommendation.ignore_form.scope." + scope)}</option>`)}
				</nb-radio-list>
			`;
		}

		return html`<nb-input name="scope" type="hidden" value="${scopeOptions[0]}"></nb-input>`;
	}

	get form()
	{
		return this.shadowRoot.querySelector("form");
	}

	async _submitCallback(e)
	{
		e.preventDefault();

		await ApiClient.post("api_testing_ignore_entry_create", new FormData(this.form));
		Modal.closeCurrent();

		window.plausible("Testing usage", { props: { action: "Ignore recommendation", recommendation: this.recommendation } });
	}
}

customElements.define("recommendation-ignore-form", RecommendationIgnoreForm);
