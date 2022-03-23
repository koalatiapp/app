import { LitElement, html, css } from "lit";
import { ApiClient } from "../../utils/api/index.js";
import initQuillEditor from "../../utils/quill/quill-init.js";
import quillStyles from "../../utils/quill/quill-css-theme.js";
import stylesReset from "../../native-bear/styles-reset.js";
export class CommentEditor extends LitElement {
	#editorId = Math.floor(Math.random() * 1000000);
	#editor = null;

	static get styles()
	{
		return [
			stylesReset,
			quillStyles,
			css`
				:host { display: block; position: relative; }

				label { font-size: 1rem; font-weight: 500; color: var(--color-gray-darker); }
				article[contenteditable] { display: block; width: 100%; max-width: 100%; min-height: 6em; padding: 1.5em 2em; margin: 6px 0; font-family: inherit; font-size: .95rem; font-weight: 400; line-height: 1.5rem; color: var(--color-gray-darker); background-color: var(--color-white); border: 2px solid var(--color-gray-light); border-radius: 8px; outline: none; box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb), .025); box-sizing: border-box; -webkit-font-smoothing: antialiased; transition: border-color .25s ease, box-shadow .25s ease; box-sizing: border-box; }
				article[contenteditable] * { max-width: 100%; line-height: 1.35; }

				article[contenteditable] code { display: block; padding: .5em .65em; margin: .5em 0; font-family: SFMono-Regular,Consolas,Liberation Mono,Menlo,monospace; font-size: .85em; background-color: rgba(27,31,35,.05); border-radius: 3px; }

				.button-container { text-align: right; }

				@media (prefers-color-scheme: dark) {
					article[contenteditable] code { background-color: rgb(146 170 255 / 10%); }
				}
			`
		];
	}

	static get properties() {
		return {
			projectId: {type: String},
			checklistItemId: {type: String},
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

	firstUpdated()
	{
		this.#editor = initQuillEditor(this.contentElement);
	}

	render()
	{
		return html`
			<link rel="stylesheet" href="https://cdn.quilljs.com/latest/quill.snow.css">
			<label for="comment-editor-${this.#editorId}">
				${Translator.trans(this.threadId ? "comment.editor_reply_label" : "comment.editor_label")}
			</label>
			<article class="comment-editor" id="comment-editor-${this.#editorId}"></article>

			<div class="button-container">
				<nb-button size="small" @click=${() => this.#submit()}>
					${Translator.trans(this.threadId ? "comment.editor_reply_submit" : "comment.editor_submit")}
				</nb-button>
			</div>
	  	`;
	}

	get contentElement()
	{
		return this.shadowRoot.querySelector(`#comment-editor-${this.#editorId}`);
	}

	get value()
	{
		const textContent = this.#editor.root.textContent.trim();
		let htmlContent = this.#editor.root.innerHTML.trim();

		if (/^\s*$/.test(textContent)
			&& htmlContent.indexOf("<img") == -1
			&& htmlContent.indexOf("<video") == -1) {
			return "";
		}

		htmlContent = htmlContent.replace(/<p><br><\/p>/g, "");

		return htmlContent;
	}

	focusEditor()
	{
		this.#editor.focus();
	}

	#clear()
	{
		this.#editor.setContents([]);
	}

	#submit()
	{
		const content = this.value;

		if (!content.length) {
			return;
		}

		ApiClient.post("api_comments_submit", {
			project_id: this.projectId ?? "",
			checklist_item_id: this.checklistItemId ?? "",
			thread_id: this.threadId ?? "",
			content: content,
		}).then(() => {
			this.#clear();
			window.Flash.show("success", Translator.trans("comment.flash.submitted"));

			this.dispatchEvent(new CustomEvent("submitted-comment"));
		});
	}

	focus()
	{
		this.contentElement.focus();
	}
}

customElements.define("comment-editor", CommentEditor);
