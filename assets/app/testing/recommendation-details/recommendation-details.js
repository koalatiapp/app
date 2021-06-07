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
				.sample-recommendation-title { padding: 1em; margin: 0; margin-bottom: 2em; font-weight: 500; color: var(--color-blue-dark-faded); background-color: var(--color-gray-light); border-radius: 3px; box-shadow: 0 2px 5px rgb(var(--shadow-rgb), .1); }
				.page-url { font-size: .8em; font-weight: 400; color: var(--color-blue-dark-faded); }
				.page-empty-state { color: var(--color-gray); }

				@media (prefers-color-scheme: dark) {
					.sample-recommendation-title { color: var(--color-gray); }
				}
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
			<div class="sample-recommendation-title">
				<nb-markdown barebones>
					<script type="text/markdown">
						${unsafeHTML(this.recommendationGroup.title)}
					</script>
				</nb-markdown>
			</div>
			<h3>${Translator.trans("recommendation.modal.description_heading")}</h3>
			<nb-markdown>
				<script type="text/markdown">
					${this.recommendationGroup.sample.parentResult.description}
				</script>
			</nb-markdown>

			<hr>

			<h3>${Translator.trans("recommendation.modal.pages_heading")}</h3>
			${this.recommendationGroup.recommendations.map(recommendation => {
				const renderedTable = this.constructor._renderTable(recommendation.parentResult.tableData);
				const renderedSnippets = this.constructor._renderSnippets(recommendation.parentResult.snippets);
				const hasDetails = renderedSnippets || renderedTable;

				return html`
					<nb-accordion ?open=${this.recommendationGroup.recommendations.indexOf(recommendation) === 0}>
						<div slot="summary">
							<div class="page-title">${recommendation.relatedPage.title || Translator.trans("page.unknown_title")}</div>
							<div class="page-url">${recommendation.relatedPage.url}</div>
						</div>

						${renderedTable}
						${renderedSnippets}
						${!hasDetails ? html`<div class="page-empty-state">${Translator.trans("recommendation.modal.no_page_details")}</div>` : ""}

						<p>${Translator.trans("recommendation.modal.last_occured_on", { "date": new Intl.DateTimeFormat("en-CA", { dateStyle: "medium", timeStyle: "medium" }).format(new Date(recommendation.dateLastOccured))})}</p>
					</nb-accordion>
				`;
			})}
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

	static _renderTable(tableData)
	{
		if (!tableData || !tableData.length) {
			return "";
		}

		let isFirstRow = true;
		const renderRow = row => {
			const tag = unsafeHTML(isFirstRow ? "th" : "td");

			return html`<tr>
				${row.map(cellContent => html`<${tag}>
					<nb-markdown barebones>
						<script type="text/markdown">
							${cellContent}
						</script>
					</nb-markdown>
				</${tag}>`)}
			</tr>`;
		};

		return html`
			<h4>${Translator.trans("recommendation.modal.table_heading")}</h4>
			<table>
				${tableData.map(renderRow)}
			</table>
		`;
	}

	static _renderSnippets(snippets)
	{
		if (!snippets || !snippets.length) {
			return "";
		}

		return html`
			<h4>${Translator.trans("recommendation.modal.snippets_heading")}</h4>
			${snippets.map(snippet => {
				return html`<nb-markdown barebones>
					<script type="text/markdown">
						\`\`\`
						${snippet}
						\`\`\`
					</script>
				</nb-markdown>`;
			})}
		`;
	}
}

customElements.define("recommendation-details", RecommendationDetails);
