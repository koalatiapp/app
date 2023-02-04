import { LitElement, html, css } from "lit";
import { ApiClient } from "../../utils/api/index.js";
import stylesReset from "../../native-bear/styles-reset.js";
import getTinyMceEditor from "../../utils/get-tinymce-editor.js";

export class CommentEditor extends LitElement {
	#editorId = Math.floor(Math.random() * 1000000);

	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: block; position: relative; }

				label { font-size: 1rem; font-weight: 500; color: var(--color-gray-darker); }
				tinymce-editor { display: block; margin: .5rem 0; }

				.button-container { text-align: right; }

				@media (prefers-color-scheme: dark) {

				}
			`
		];
	}

	static get properties() {
		return {
			projectId: {type: String},
			checklistItemIri: {type: String},
			threadId: {type: String},
			content: {type: String},
		};
	}

	constructor()
	{
		super();
		this.commentId = null;
	}

	connectedCallback()
	{
		super.connectedCallback();
	}

	render()
	{
		return html`
			<label for="comment-editor-${this.#editorId}">
				${Translator.trans(this.threadId ? "comment.editor_reply_label" : "comment.editor_label")}
			</label>
			${getTinyMceEditor(this.#editorId)}

			<div class="button-container">
				<nb-button size="small" @click=${() => this.#submit()}>
					${Translator.trans(this.threadId ? "comment.editor_reply_submit" : "comment.editor_submit")}
				</nb-button>
			</div>
	  	`;
	}

	get #editor()
	{
		return this.shadowRoot.querySelector("tinymce-editor");
	}

	get value()
	{
		let htmlContent = this.#editor.value.trim();
		htmlContent = htmlContent.replace(/\\n<p>&nbsp;<\/p>/g, "");

		return htmlContent;
	}

	focusEditor()
	{
		// If the editor isn't initialized yet, focus won't work.
		if (!this.#editor.classList.contains("ready")) {
			setTimeout(() => this.focusEditor(), 20);
			return;
		}

		this.#editor._editor.focus();
	}

	#clear()
	{
		this.#editor.value = "";
	}

	#submit()
	{
		const content = this.value;

		if (!content.length) {
			return;
		}

		const payload = {
			content: content,
		};

		if (this.projectId) {
			payload.project = `/api/projects/${this.projectId}`;
		}
		if (this.checklistItemIri) {
			payload.checklist_item = this.checklistItemIri;
		}
		if (this.threadId) {
			payload.thread = `/api/comments/${this.threadId}`;
		}

		ApiClient.post("/api/comments", payload).then(() => {
			this.#clear();
			window.Flash.show("success", Translator.trans("comment.flash.submitted"));

			this.dispatchEvent(new CustomEvent("submitted-comment"));

			window.plausible("Checklist usage", { props: { action: "Submitted comment" } });
		});
	}

	focus()
	{
		this.#editor.focus();
	}
}

customElements.define("comment-editor", CommentEditor);
