import escapeHtml  from "escape-html";
import { LitElement, html, css } from "lit";
import stylesReset from "../../native-bear/styles-reset.js";
import { ApiClient } from "../../utils/api/index.js";
import faImport from "../../utils/fontawesome-import";

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

				table { width: 100%; table-layout: fixed; }
				th { padding: .5rem; text-align: left; vertical-align: bottom; }
				td { padding: .5rem; vertical-align: top; border-bottom: 2px solid var(--color-gray-light); background-color: var(--color-white); }
				td img { max-width: 200px; }

				@media (prefers-color-scheme: dark) {
					.sample-recommendation-title { color: var(--color-gray); }
				}
			`
		];
	}

	static get properties() {
		return {
			recommendationId: {type: String},
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
						${this.recommendationGroup.title}
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
				const renderedTable = this.constructor._renderTable(recommendation.parentResult.dataTable);
				const renderedSnippets = this.constructor._renderSnippets(recommendation.parentResult.snippets);
				const hasDetails = renderedSnippets || renderedTable;

				return html`
					<nb-accordion ?open=${this.recommendationGroup.recommendations.indexOf(recommendation) === 0}>
						<div slot="summary">
							<div class="page-title">${escapeHtml(recommendation.relatedPage.title) || Translator.trans("page.unknown_title")}</div>
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

	_loadRecommendationGroup()
	{
		this._loading = true;

		return new Promise(resolve => {
			ApiClient.get("api_testing_recommendation_group_details", { id: this.recommendationId }, null)
				.then(response => {
					resolve(response.data);
				}).catch(() => {
					resolve(null);
				}).finally(() => {
					this._loading = false;
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
			if (isFirstRow) {
				isFirstRow = false;

				return html`<tr>
					${row.map(cellContent => html`<th>
						<nb-markdown barebones custom-css="img { min-width: 50px; max-width: min(250px, 100%); } a { word-break: break-all; }">
							<script type="text/markdown">
								${cellContent}
							</script>
						</nb-markdown>
					</th>`)}
				</tr>`;
			}

			return html`<tr>
				${row.map(cellContent => html`<td>
					<nb-markdown barebones custom-css="img { min-width: 50px; max-width: min(250px, 100%); } a { word-break: break-all; }">
						<script type="text/markdown">
							${cellContent}
						</script>
					</nb-markdown>
				</td>`)}
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
