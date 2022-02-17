import * as timeago from "timeago.js";
import { LitElement, html, css } from "lit";
import { ApiClient } from "../../utils/api/index.js";
import stylesReset from "../../native-bear/styles-reset.js";
import fontAwesomeImport from "../../utils/fontawesome-import.js";

export class UserComment extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: block; padding: 1rem; border: 1px solid var(--color-gray-light); border-radius: .5rem; box-shadow: 0 0 0.5em rgba(var(--shadow-rgb), 0.05); }

				.header { display: flex; justify-content: space-between; gap: 1em; }
				.avatar { flex-shrink: 0; width: 2.5em; height: 2.5em; object-fit: cover; border-radius: 50%; }
				.heading { flex: 1; align-self: center; white-space: nowrap; overflow: hidden; }
				.author { font-size: 1.05em; font-weight: 700; text-overflow: ellipsis; }
				.date { font-size: .8em; color: var(--color-gray-dark); }
				.actions { flex-shrink: 0; text-align: right; }

				.body { margin-top: 1.5em; font-size: 1em; }


				@media (prefers-color-scheme: dark) {

				}
			`
		];
	}

	static get properties() {
		return {
			commentId: {type: String},
			createdDate: {type: Date},
			authorName: {type: String},
			content: {type: String},
			isResolved: {type: Boolean},
			replies: {type: Array},
			_loaded: {state: true},
		};
	}

	constructor()
	{
		super();
		this.data = null;
		this.commentId = null;
		this.dateCreated = new Date();
		this.authorAvatarUrl = this.placeholderUrl;
		this.authorName = "";
		this.content = "";
		this.isResolved = false;
		this.replies = [];
		this._loaded = false;
	}

	firstUpdated()
	{
		this.load();
	}

	render()
	{
		if (!this._loaded) {
			return html`<nb-loading-spinner></nb-loading-spinner>`;
		}

		return html`
			${fontAwesomeImport}

			<div class="header">
				<img src=${this.authorAvatarUrl} alt="" class="avatar">
				<div class="heading">
					<div class="author">${this.authorName}</div>
					<div class="date">${timeago.format(this.dateCreated)}</div>
				</div>
				<div class="actions">
					<nb-button size="tiny" color="gray">
						Reply
					</nb-button>
					<nb-button size="tiny">
						Resolve
					</nb-button>
				</div>
			</div>

			<div class="body">
				${this.content}
			</div>

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

		ApiClient.get("api_comments_details", { id: this.commentId }).then(response => {
			this._loadData(response.data);
		});
	}

	_loadData(data)
	{
		this.commentId = data.id;
		this.dateCreated = new Date(data.dateCreated);
		this.authorAvatarUrl = data.author?.avatarUrl ?? this.placeholderUrl;
		this.authorName = data.authorName;
		this.content = data.content;
		this.isResolved = data.isResolved;
		this.replies = [];
		this._loaded = true;
	}

	get placeholderUrl()
	{
		return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mO8effVfwAI2AOhL4TQ4QAAAABJRU5ErkJggg==";
	}
}

customElements.define("user-comment", UserComment);
