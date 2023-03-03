import { LitElement, html } from "lit";
import { InternalApiClient } from "../../utils/internal-api";

export class HistoricalUsageTable extends LitElement {
	#monthsPerRequest = 5;

	// Use light DOM instead of shadow DOM
	createRenderRoot() {
		return this;
	}

	static get properties() {
		return {
			usage: {state: true},
			canLoadMore: {state: true},
		};
	}

	constructor()
	{
		super();
		this.usage = null;
		this.canLoadMore = false;
	}

	connectedCallback()
	{
		super.connectedCallback();

		InternalApiClient.get("api_user_usage_historical")
			.then(response => {
				this.usage = response.data;
				this.canLoadMore = this.usage.length == this.#monthsPerRequest;
			});
	}

	render()
	{
		const tableStyles = "width: min(650px, 100%);";
		const tableHeading = html`
			<thead>
				<tr>
					<th>${Translator.trans("user_settings.quota.label.start_date")}</th>
					<th>${Translator.trans("user_settings.quota.label.end_date")}</th>
					<th>${Translator.trans("user_settings.quota.label.billing_date")}</th>
					<th>${Translator.trans("user_settings.quota.label.usage")}</th>
				</tr>
			</thead>
		`;

		if (this.usage === null || this.usage.length == 0) {
			return html`
				<table class="simple-table" style=${tableStyles}>
					${tableHeading}
					<tbody>
						<tr>
							<td colspan="4">
								${this.usage === null ? html`<nb-loading-spinner></nb-loading-spinner>` : Translator.trans("user_settings.quota.history.no_data")}
							</td>
						</tr>
					</tbody>
				</table>
			`;
		}

		const dateFormatter = new Intl.DateTimeFormat("en-CA", { year: "numeric", month: "long", day: "numeric" });
		const numberFormatter = new Intl.NumberFormat("en-CA", { });

		return html`
			<table class="simple-table" style=${tableStyles}>
				${tableHeading}
				<tbody>
					${this.usage.map(usageCycle => html`
						<tr>
							<td>${dateFormatter.format(new Date(usageCycle.usageCycleStartDate))}</td>
							<td>${dateFormatter.format(new Date(usageCycle.usageCycleEndDate))}</td>
							<td>${dateFormatter.format(new Date(usageCycle.usageCycleBillingDate))}</td>
							<td>${numberFormatter.format(usageCycle.pageTestUsage)}</td>
						</tr>
					`)}
				</tbody>
			</table>
			<hr class="spacer small">
			${this.canLoadMore ? html`
				<nb-button color="gray" size="small" @click=${() => this.#loadMore()}>
					${Translator.trans("generic.load_more")}
				</nb-button>
			` : ""}
	  	`;
	}

	#loadMore() {
		if (this.usage === null || this.usage.length === 0) {
			this.canLoadMore = false;
			return;
		}

		const loadMoreButton = this.querySelector("nb-button");
		const previousDate = this.usage.at(-1).usageCycleStartDate;

		loadMoreButton.toggleAttribute("loading", true);

		InternalApiClient.get("api_user_usage_historical", {
			previousDate,
			limit: this.#monthsPerRequest,
		}).then(response => {
			loadMoreButton.removeAttribute("loading");
			this.usage = this.usage.concat(response.data);
			this.canLoadMore = response.data.length == this.#monthsPerRequest;
		});
	}
}

customElements.define("historical-usage-table", HistoricalUsageTable);
