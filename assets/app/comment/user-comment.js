import * as timeago from "timeago.js";
import { LitElement, html, css } from "lit";
import { unsafeHTML } from "lit/directives/unsafe-html.js";
import { repeat } from "lit/directives/repeat.js";
import { ApiClient } from "../../utils/api/index.js";
import MercureClient from "../../utils/mercure-client.js";
import stylesReset from "../../native-bear/styles-reset.js";
import fontAwesomeImport from "../../utils/fontawesome-import.js";

const cachedComments = {};

export class UserComment extends LitElement {
	#mercureUpdateCallback = null;

	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: block; padding: 1rem; background-color: var(--color-white); border: 1px solid var(--color-gray-light); border-radius: .5rem; box-shadow: 0 0 0.5em rgba(var(--shadow-rgb), 0.05); }

				.header { display: flex; justify-content: space-between; gap: 1em; }
				.avatar { flex-shrink: 0; width: 2.5em; height: 2.5em; object-fit: cover; border-radius: 50%; }
				.heading { flex: 1; align-self: center; white-space: nowrap; overflow: hidden; }
				.author { font-size: 1.05em; font-weight: 700; text-overflow: ellipsis; }
				.date { font-size: .8em; color: var(--color-gray-dark); }
				.actions { flex-shrink: 0; text-align: right; }
				.resolved { display: inline-block; padding: 0.5em 0.75em; font-size: 0.8rem; font-weight: 500; color: #269900; background-color: var(--color-green-10); border-radius: 0.5em; cursor: default; }

				.body { margin-top: 1.5em; font-size: 1em; }
				.body * { max-width: 100%; }

				.body code { display: block; padding: .5em .65em; margin: .5em 0; font-family: SFMono-Regular,Consolas,Liberation Mono,Menlo,monospace; font-size: .85em; background-color: rgba(27,31,35,.05); border-radius: 3px; overflow: auto; }
				.body blockquote { padding: 0.25em 1em; margin: 0.5em 0; color: var(--color-gray-dark); border-left: 4px solid var(--color-gray); }
				.body table { width: 100%; table-layout: fixed; border-collapse: collapse; }
				.body td,
				.body th { padding: 0.5ch 0.75ch; vertical-align: top; border: 1px solid var(--color-blue-lighter); }

				details { margin-top: 1.5em; }
				summary { color: var(--color-blue); cursor: pointer; }
				summary:hover { color: var(--color-black); }
				.replies { display: flex; flex-direction: column; padding-left: 0; margin: 0; list-style: none; }
				.replies li { padding-left: 2rem; margin-top: 1rem; background-image: url("/ext/fontawesome/svgs/regular/arrow-turn-down-right.svg"); background-size: 1rem; background-position: .5rem .5rem; background-repeat: no-repeat; }

				comment-editor { margin-top: 1.5em; margin-left: 2em; }

				@media (prefers-color-scheme: dark) {
					.resolved { color: #d7ffcd; background-color: var(--color-green-50); }

					.body code { background-color: rgb(146 170 255 / 10%); }
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
			thread: {type: Array},
			replies: {type: Array},
			showReplies: {type: Boolean},
			autoShowReplies: {type: Boolean},
			showReplyEditor: {type: Boolean},
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
		this.thread = null;
		this.replies = [];
		this.showReplies = true;
		this.autoShowReplies = false;
		this.showReplyEditor = false;
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

			<div class="header">
				<img src=${this.authorAvatarUrl} alt="" class="avatar">
				<div class="heading">
					<div class="author">${this.authorName}</div>
					<div class="date">${timeago.format(this.dateCreated)}</div>
				</div>
				<div class="actions">
					${!this.isResolved && !this.thread?.isResolved ? html`
						<nb-button size="tiny" color="gray" @click=${() => this.toggleReplyEditor(true)}>
							${Translator.trans("comment.reply")}
						</nb-button>
					` : ""}

					${!this.thread && !this.isResolved ?
						html`
							<nb-button size="tiny" class="resolve" @click=${() => this.#resolve()}>
								${Translator.trans("comment.resolve")}
							</nb-button>
						` : ""
					}

					${!this.thread && this.isResolved ?
						html`
							<span class="resolved">
								<i class="fas fa-circle-check"></i>
								&nbsp;
								${Translator.trans("comment.resolved")}
							</span>
						` : ""
					}
				</div>
			</div>

			<div class="body">
				${unsafeHTML(this.content)}
			</div>

			${!!this.showReplies && this.replies.length ? html`
				<details ?open=${!!this.autoShowReplies}>
					<summary>${Translator.transChoice("comment.view_replies", this.replies.length, { "%count%": this.replies.length })}</summary>
					<ol class="replies" slot="replies">
						${repeat(
							this.replies,
							reply => reply.id,
							reply => html`<li><user-comment .data=${reply}></user-comment></li>`
						)}
					</ol>
				</details>
			` : ""}

			${this.showReplyEditor ? html`
				<br>
				<comment-editor projectId=${this.data.project.id}
					checklistItemId=${this.data?.checklistItem?.id || ""}
					threadId=${this.commentId}
					@submitted-comment=${() => this.showReplyEditor = false}>
				</comment-editor>
			` : ""}
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
		let thread = data.thread;

		if (typeof thread == "string" && thread in cachedComments) {
			thread = cachedComments[thread];
		}

		this.data = data;
		this.commentId = data.id;
		this.thread = thread;
		this.dateCreated = new Date(data.dateCreated);
		this.authorAvatarUrl = data.author?.avatarUrl ?? this.placeholderUrl;
		this.authorName = data.authorName;
		this.content = data.content;
		this.isResolved = data.isResolved;
		this.replies = Object.values(data.replies);
		this._loaded = true;

		cachedComments[this.commentId] = data;
	}

	#resolve()
	{
		const resolveButton = this.shadowRoot.querySelector("nb-button.resolve");
		resolveButton.loading = true;

		ApiClient.patch("api_comments_resolve", { id: this.commentId }).then(response => {
			this.isResolved = response.data.isResolved;
		}).finally(() => {
			resolveButton.loading = false;
		});

		window.plausible("Checklist usage", { props: { action: "Resolved comment" } });
	}

	toggleReplyEditor(showEditor)
	{
		let commentElement = this;

		if (this.thread) {
			commentElement = this.getRootNode().host;
		}

		commentElement.showReplyEditor = showEditor;

		if (showEditor) {
			commentElement.updateComplete.then(() => {
				const editor = commentElement.shadowRoot.querySelector("comment-editor");
				editor.scrollIntoView();
				editor.focusEditor();
			});
		}
	}

	#initLiveUpdateListener()
	{
		this.#mercureUpdateCallback = (update) => {
			if (update.data.id != this.commentId &&
				update.data.thread?.id != this.commentId) {
				return;
			}

			if (update.data.id == this.commentId && update.event == "update") {
				this._loadData(update.data);
				return;
			}

			// This update is about a reply to this thread
			if (update.data.thread?.id == this.commentId) {
				switch (update.event) {
				case "delete":
					this.replies = this.replies.filter(reply => reply.id == update.data.id);
					break;

				case "create":
					this.replies.push(update.data);
					this.requestUpdate("replies");
					break;

				case "update":
					this.replies = this.replies.map(reply => reply.id == update.data.id ? update.data : reply);
					this.requestUpdate("replies");
					break;
				}
			}
		};
		MercureClient.subscribe("Comment", this.#mercureUpdateCallback);
	}

	get placeholderUrl()
	{
		return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mO8effVfwAI2AOhL4TQ4QAAAABJRU5ErkJggg==";
	}
}

customElements.define("user-comment", UserComment);
