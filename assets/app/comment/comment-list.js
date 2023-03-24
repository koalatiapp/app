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
			checklistItemIri: {type: String},
			threads: {type: Array},
			_loaded: {state: true},
		};
	}

	constructor()
	{
		super();
		this.projectId = null;
		this.checklistItemIri = null;
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
					thread => html`<li><user-comment .data=${thread} ?autoShowReplies=${!thread.is_resolved}></user-comment></li>`
				)}
			</ol>

			<br>

			<comment-editor projectId=${this.projectId} checklistItemIri=${this.checklistItemIri}></comment-editor>
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

		if (this.checklistItemIri) {
			params.checklist_item = this.checklistItemIri;
		}

		ApiClient.get(`/api/projects/${this.projectId}/comments`, params).then(response => {
			this._loadData(response["hydra:member"]);
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
			if (this.projectId != update.data.project.split("/").pop()) {
				return;
			}

			if (this.checklistItemIri && this.checklistItemIri != update.data.checklist_item) {
				return;
			}

			// Reply updates and deletions are handled in the user-comment component
			if (update.data.thread !== null) {
				return;
			}

			switch (update.event) {
			case "delete":
				this.threads = this.threads.filter(thread => thread.id != update.data.id);
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
