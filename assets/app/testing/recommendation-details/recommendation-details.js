import { LitElement, html, css } from "lit";
import { unsafeHTML } from "lit/directives/unsafe-html.js";
import stylesReset from "../../../native-bear/styles-reset.js";
import faImport from "../../../utils/fontawesome-import";

export class RecommendationDetails extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: block; }
				.sample-recommendation-title { padding: 1em; margin: 0; margin-bottom: 2em; font-weight: 500; color: var(--color-blue-dark-faded); background-color: var(--card-secondary-bg-color); border-radius: 3px; }
			`
		];
	}

	static get properties() {
		return {
			recommendationId: {type: Number},
			recommendationGroup: {attribute: false},
			_loading: {state: true},
		};
	}

	constructor()
	{
		super();
		this._loading = false;
		this.recommendationId = null;
		this.recommendationGroup = null;
		this.setAttribute("aria-live", "polite");
	}

	get _loading()
	{
		return this.__loading;
	}

	set _loading(state)
	{
		const originalState = this.__loading;
		this.__loading = state;
		this.setAttribute("aria-busy", state ? "true" : "false");
		this.requestUpdate("_loading", originalState);
	}

	connectedCallback()
	{
		super.connectedCallback();

		if (!this.recommendationGroup && !this._loading) {
			this._loadRecommendationGroup().then(recommendationGroup => {
				this.recommendationGroup = recommendationGroup;
			});
		}
	}

	render()
	{
		if (this._loading) {
			return html`
				<nb-loading-spinner></nb-loading-spinner>
			`;
		}

		if (!this.recommendationGroup) {
			return html`
				<p>${Translator.trans("recommendation.modal.not_found")}</p>
			`;
		}

		return html`
			${faImport}
			<div class="sample-recommendation-title">${unsafeHTML(this.recommendationGroup.htmlTitle)}</div>
			<h3>${Translator.trans("recommendation.modal.description_heading")}</h3>
			<nb-markdown>
				<script type="text/markdown">
					${this.recommendationGroup.sample.parentResult.description}
				</script>
			</nb-markdown>
			<nb-button href="#" target="_blank" size="small" color="gray">
				${Translator.trans("recommendation.modal.learn_more")}
				&nbsp;
				<i class="far fa-up-right-from-square"></i>
			</nb-button>

			<hr>

			<h3>${Translator.trans("recommendation.modal.pages_heading")}</h3>
			<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quam iste dolorem accusamus quisquam sed, laboriosam quo reiciendis! Temporibus illo omnis recusandae expedita aut a sunt maxime blanditiis, dignissimos asperiores nemo?</p>

			<p>{# TODO: Add recommendation details (snippets, table) for each page #}</p>
			<p>{# TODO: Add a section that shows other recommendations from the same test/tool #}</p>
		`;
	}

	get contentUrl()
	{
		return Routing.generate("api_testing_recommendation_group", {recommendationId: this.recommendationId});
	}

	_loadRecommendationGroup()
	{
		this._loading = true;

		return new Promise(resolve => {
			fetch(this.contentUrl)
				.then(response => response.json())
				.then(response => {
					this._loading = false;

					if (response.status == "ok") {
						resolve(response.data);
						return;
					}

					resolve(null);
				});
		});
	}
}

customElements.define("recommendation-details", RecommendationDetails);
