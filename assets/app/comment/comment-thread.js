import { LitElement, html, css } from "lit";
import { repeat } from "lit/directives/repeat.js";
import { ApiClient } from "../../utils/api/index.js";
import stylesReset from "../../native-bear/styles-reset.js";
import fontAwesomeImport from "../../utils/fontawesome-import.js";

export class CommentThread extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: block; }

				.replies { display: flex; flex-direction: column; padding-left: 0; margin: 0; list-style: none; }
				.replies li { padding-left: 2rem; margin-top: 1rem; background-image: url("/ext/fontawesome/svgs/regular/arrow-turn-down-right.svg"); background-size: 1rem; background-position: .5rem .5rem; background-repeat: no-repeat; }

				@media (prefers-color-scheme: dark) {

				}
			`
		];
	}

	static get properties() {
		return {
			commentId: {type: String},
			comment: {type: Array},
			replies: {type: Array},
			_loaded: {state: true},
		};
	}

	constructor()
	{
		super();
		this.data = null;
		this.commentId = null;
		this.comment = null;
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

			<user-comment .data=${this.comment}></user-comment>

			<ol class="replies">
				${repeat(
					this.replies,
					reply => reply.id,
					reply => html`<li><user-comment .data=${reply}></user-comment></li>`
				)}
			</ol>
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
		this.comment = data;
		this.replies = data?.replies ?? [];
		this._loaded = true;
	}
}

customElements.define("comment-thread", CommentThread);
