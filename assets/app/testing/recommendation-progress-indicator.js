import { ApiClient } from "../../utils/api";
import MercureClient from "../../utils/mercure-client";
import { LitElement, html, css } from "lit";
import faImport from "../../utils/fontawesome-import.js";
import querySelectorAllAnywhere from "../../utils/query-selector-all-anywhere.js";

export class RecommendationProgressIndicator extends LitElement {
	#mercureUpdateCallback = null;

	static get styles()
	{
		return css`
			div:first-child,
			div:not(:last-child) { margin-bottom: 1em; }
			div:not(:first-child) { margin-top: 1em; }

			.timer { font-size: 1.1em; font-weight: 700; color: var(--color-blue-faded); line-height: 1.35; }
			.timer .label { display: block; font-size: .8em; font-weight: 400; line-height: 1.35; }
			.unknown-time-estimate { font-size: 3rem; }
		`;
	}

	static get properties() {
		return {
			...super.properties,
			projectId: { type: String },
			hasRequestsPending: { type: Boolean },
			pendingRequestCount: { type: Number },
			timeLeftInMs: { type: Number },
			_hasReceivedFirstResponse: { state: true },
		};
	}

	constructor()
	{
		super();
		this.reset();
	}

	disconnectedCallback()
	{
		if (this.#mercureUpdateCallback) {
			MercureClient.unsubscribe(this.supportedEntityType(), this.#mercureUpdateCallback);
		}

		super.disconnectedCallback();
	}

	firstUpdated()
	{
		super.firstUpdated();
		this.fetchStatus();
		this._initStatusUpdateListener();
	}

	render()
	{
		if (!this._hasReceivedFirstResponse) {
			return html`
				<nb-loading-spinner></nb-loading-spinner>
				<div>${Translator.trans("automated_testing.progress.loading_first_results")}</div>
			`;
		}

		if (!this.pageCount) {
			return html`
				<nb-loading-spinner></nb-loading-spinner>
				<div>${Translator.trans("automated_testing.progress.no_pages_yet")}</div>
			`;
		}

		if (this.hasRequestsPending) {
			return html`
				<nb-loading-spinner></nb-loading-spinner>
				<div>${Translator.trans("automated_testing.progress.scanning_status_indicator")}</div>
				<div class="timer">
					<span class="label">${Translator.trans("automated_testing.progress.time_left")}:</span>
					${this.timeLeftInMs > 0 ? this.getFormattedTimeLeft() : html`<span class="unknown-time-estimate">🤷</span>`}
				</div>
			`;
		}

		return html`
			${faImport}
			<i class="fas fa-circle-check fa-3x"></i>
			<div>${Translator.trans("automated_testing.progress.results_below")}</div>

			<request-testing-button size="small" color="gray" projectId="${this.projectId}">
				<span>
					${Translator.trans("recommendation.run_again")}
					&nbsp;
				</span>
				<i class="far fa-rotate"></i>
			</request-testing-button>
		`;
	}

	getFormattedTimeLeft()
	{
		const totalSeconds = Math.round(this.timeLeftInMs / 1000);
		const hours   = Math.floor(totalSeconds / 3600);
		const minutes = Math.floor(totalSeconds / 60) % 60;
		const seconds = totalSeconds % 60;
		const parts = [];

		if (hours) {
			parts.push(Translator.trans("generic.time.short.hours", { hours: hours }));
			parts.push(Translator.trans("generic.time.short.minutes", { minutes: minutes }));
		} else if (minutes) {
			parts.push(Translator.trans("generic.time.short.minutes", { minutes: minutes }));
			parts.push(Translator.trans("generic.time.short.seconds", { seconds: seconds }));
		} else {
			parts.push(Translator.trans("generic.time.short.seconds", { seconds: seconds }));
		}

		return parts.join(" ");
	}

	/**
	 * Fetches the testing status from the API
	 * and updates the indicator accordingly.
	 */
	fetchStatus()
	{
		// If we're already waiting for a response from the server,
		// let's avoid sending another request.
		if (this._isWaitingForServerResponse) {
			return;
		}

		this._isWaitingForServerResponse = true;
		this._lastFetchTimestamp = Date.now();

		ApiClient.get("api_testing_request_project_status", { id: this.projectId }, null)
			.then(response => {
				this._handleStatusUpdate(response.data);
			})
			.catch((error) => {
				console.error(error);
			})
			.finally(() => {
				this._isWaitingForServerResponse = false;
			});
	}

	/**
	 * Resets the progress indicator to its initial state.
	 */
	reset()
	{
		this._clearTimerInterval();
		this.pageCount = 0;
		this.activePageCount = 0;
		this.hasRequestsPending = false;
		this.pendingRequestCount = 0;
		this.timeLeftInMs = 0;
		this._isWaitingForServerResponse = false;
		this._hasReceivedFirstResponse = false;
		this._timerInterval = null;
		this._lastFetchTimestamp = null;
	}

	_handleStatusUpdate(status)
	{
		this._hasReceivedFirstResponse = true;
		this.pageCount = status.pageCount;
		this.activePageCount = status.activePageCount;
		this.hasRequestsPending = status.pending;
		this.pendingRequestCount = status.requestCount;
		this.timeLeftInMs = status.timeEstimate;
		this._startTimerInterval();

		for (const refreshButton of querySelectorAllAnywhere("request-testing-button")) {
			refreshButton.loading = this.hasRequestsPending;
		}
	}

	_initStatusUpdateListener()
	{
		this.#mercureUpdateCallback = (update) => {
			if (update.id != this.projectId) {
				return;
			}

			const status = update.data;

			if (typeof status.requestCount != "undefined") {
				this._handleStatusUpdate(status);
			} else if (status.pending && !this._hasReceivedFirstResponse) {
				this.fetchStatus();
			}
		};
		MercureClient.subscribe("TestingStatus", this.#mercureUpdateCallback);
	}

	// Timer related methods

	_clearTimerInterval()
	{
		if (this._timerInterval) {
			clearInterval(this._timerInterval);
			this._timerInterval = null;
		}
	}

	_startTimerInterval()
	{
		this._clearTimerInterval();
		this._timerInterval = setInterval(() => {
			const timeSinceLastFetch = Date.now() - this._lastFetchTimestamp;
			this.timeLeftInMs = Math.max(0, this.timeLeftInMs - 1000);

			if (this.timeLeftInMs == 0 && this.hasRequestsPending && timeSinceLastFetch > 10000) {
				this.fetchStatus();
			}
		}, 1000);
	}
}

customElements.define("recommendation-progress-indicator", RecommendationProgressIndicator);
