import { ApiClient } from "../../utils/api";
import { LitElement, html, css } from "lit";
import faImport from "../../utils/fontawesome-import.js";
import querySelectorAllAnywhere from "../../utils/query-selector-all-anywhere.js";

export class RecommendationProgressIndicator extends LitElement {
	static get styles()
	{
		return css`
			div:first-child,
			div:not(:last-child) { margin-bottom: 1em; }
			div:not(:first-child) { margin-top: 1em; }

			.timer { font-size: 1.1em; font-weight: 700; color: var(--color-blue-faded); line-height: 1.35; }
			.timer .label { display: block; font-size: .8em; font-weight: 400; line-height: 1.35; }
		`;
	}

	static get properties() {
		return {
			...super.properties,
			projectId: { type: String },
			hasRequestsPending: { type: Boolean },
			pendingRequestCount: { type: Number },
			timeLeftInMs: { type: Number },
			_loaded: { state: true },
		};
	}

	constructor()
	{
		super();
		this.hasRequestsPending = false;
		this.pendingRequestCount = 0;
		this.timeLeftInMs = 0;
		this._loading = false;
		this._loaded = false;
		this._timerInterval = null;
		this._nextRefreshTimeout = null;
		this._nextRefreshTimestamp = null;
	}

	firstUpdated()
	{
		super.firstUpdated();
		this.fetchStatus();
	}

	render()
	{
		if (!this._loaded) {
			return html`
				<nb-loading-spinner></nb-loading-spinner>
				<div>${Translator.trans("automated_testing.progress.loading_first_results")}</div>
			`;
		}

		if (this.hasRequestsPending) {
			return html`
				<nb-loading-spinner></nb-loading-spinner>
				<div>${Translator.trans("automated_testing.progress.scanning_status_indicator")}</div>
				<div class="timer">
					<span class="label">${Translator.trans("automated_testing.progress.time_left")}:</span>
					${this.getFormattedTimeLeft()}
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
		this._loading = true;

		ApiClient.get("api_testing_request_project_status", { id: this.projectId }, null)
			.then(response => {
				this._loading = false;
				this._loaded = true;
				this.hasRequestsPending = response.data.pending;
				this.pendingRequestCount = response.data.requestCount;
				this.timeLeftInMs = response.data.timeEstimate;

				for (const refreshButton of querySelectorAllAnywhere("request-testing-button")) {
					refreshButton.loading = this.hasRequestsPending;
				}

				if (this.hasRequestsPending) {
					this._scheduleNextRefresh();
				}
			})
			.catch((error) => {
				console.error(error);

				setTimeout(() => this.fetchStatus(), 10000);
			});
	}

	/**
	 * Signals to the progress indicator that the status
	 * has likely changed, and that it should soon look
	 * into re-fetching the status from the server.
	 */
	requestStatusUpdate()
	{
		const delay = 5000;
		const timestampAfterDelay = (new Date()).getTime() + delay;

		if (this._nextRefreshTimestamp && timestampAfterDelay > this._nextRefreshTimestamp) {
			return;
		}

		this._scheduleNextRefresh(delay);
	}

	/**
	 * Initializes a countdown for the timer and queues
	 * a `fetchStatus()` call in a specified (or not)
	 * amount of time.
	 *
	 * @param {Number|null} delay
	 */
	_scheduleNextRefresh(delay = null)
	{
		this._startTimerInterval();

		if (!delay) {
			delay = this.timeLeftInMs / Math.max(1, this.pendingRequestCount);
		}

		delay = Math.max(delay, 1000);

		setTimeout(() => {
			this._nextRefreshTimestamp = null;
			this.fetchStatus();
		}, delay);

		this._nextRefreshTimestamp = (new Date()).getTime() + delay;
	}

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
			this.timeLeftInMs = Math.max(0, this.timeLeftInMs - 1000);
		}, 1000);
	}
}

customElements.define("recommendation-progress-indicator", RecommendationProgressIndicator);
