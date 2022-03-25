import { LitElement, html, css } from "lit";
import { repeat } from "lit/directives/repeat.js";
import { ApiClient } from "../../utils/api/index.js";
import MercureClient from "../../utils/mercure-client.js";
import stylesReset from "../../native-bear/styles-reset.js";
import fontAwesomeImport from "../../utils/fontawesome-import.js";

export class CommentList extends LitElement {
	#mercureUpdateCallback = null;

	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: block; }

				.threads { display: flex; flex-direction: column; gap: 1rem; padding-left: 0; margin: 0; list-style: none; }

				comment-editor { margin-bottom: 25vh; }

				@media (prefers-color-scheme: dark) {

				}
			`
		];
	}

	static get properties() {
		return {
			projectId: {type: String},
			checklistItemId: {type: String},
			threads: {type: Array},
			_loaded: {state: true},
		};
	}

	constructor()
	{
		super();
		this.projectId = null;
		this.checklistItemId = null;
		this.threads = [];
		this._loaded = false;
	}

	firstUpdated()
	{
		this.load();
		this.#initLiveUpdateListener();
	}

	disconnectedCallback()
	{
		if (this.#mercureUpdateCallback) {
			MercureClient.unsubscribe("Comment", this.#mercureUpdateCallback);
		}

		super.disconnectedCallback();
	}

	render()
	{
		if (!this._loaded) {
			return html`<nb-loading-spinner></nb-loading-spinner>`;
		}

		return html`
			${fontAwesomeImport}

			<ol class="threads">
				${repeat(
					this.threads,
					thread => thread.id,
					thread => html`<li><user-comment .data=${thread} ?autoShowReplies=${!thread.isResolved}></user-comment></li>`
				)}
			</ol>

			<br>

			<comment-editor projectId=${this.projectId} checklistItemId=${this.checklistItemId}></comment-editor>
	  	`;
	}

	load()
	{
		if (this._loaded) {
			return;
		}

		if (this.data) {
			this._loadData(this.data);
			return;
		}

		const params = { };

		if (this.checklistItemId) {
			params.checklist_item_id = this.checklistItemId;
		}

		if (this.projectId) {
			params.project_id = this.projectId;
		}

		ApiClient.get("api_comments_list", params).then(response => {
			this._loadData(response.data);
		});
	}

	_loadData(data)
	{
		this.threads = Object.values(data);
		this._loaded = true;

		this.updateComplete.then(() => {
			this.dispatchEvent(new CustomEvent("comments-initialized"));
		});
	}

	#initLiveUpdateListener()
	{
		this.#mercureUpdateCallback = (update) => {
			if (this.projectId != update.data.project.id) {
				return;
			}

			if (this.checklistItemId && this.checklistItemId != update.data.checklistItem?.id) {
				return;
			}

			// Reply updates and deletions are handled in the user-comment component
			if (update.data.thread !== null) {
				return;
			}

			switch (update.event) {
			case "delete":
				this.threads = this.threads.filter(thread => thread.id == update.data.id);
				break;

			case "create":
				this.threads.push(update.data);
				this.requestUpdate("threads");
				break;
			}
		};
		MercureClient.subscribe("Comment", this.#mercureUpdateCallback);
	}
}

customElements.define("comment-list", CommentList);
