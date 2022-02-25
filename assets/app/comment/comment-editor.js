import { LitElement, html, css } from "lit";
import stylesReset from "../../native-bear/styles-reset.js";
import "@papyrs/stylo";


export class CommentEditor extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: block; margin-bottom: 220px; position: relative; }

				label { font-size: 1rem; font-weight: 500; color: var(--color-gray-darker); }
				article[contenteditable] { display: block; width: 100%; max-width: 100%; min-height: 6em; padding: 1.5em 2em; margin: 6px 0; font-family: inherit; font-size: .95rem; font-weight: 400; line-height: 1.5rem; color: var(--color-gray-darker); background-color: var(--color-white); border: 2px solid var(--color-gray-light); border-radius: 8px; outline: none; box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb), .025); box-sizing: border-box; -webkit-font-smoothing: antialiased; transition: border-color .25s ease, box-shadow .25s ease; box-sizing: border-box; }
				article[contenteditable] * { line-height: 1.35; }
				.stylo-container > *:after { display: none; }

				@media (prefers-color-scheme: dark) {

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

	firstUpdated()
	{
		this.styloEditor.containerRef = this.contentElement;
	}

	render()
	{
		return html`
			<label for="comment-editor">${Translator.trans("comment.editor_label")}</label>
			<article contenteditable="true" id="comment-editor"></article>
			<stylo-editor></stylo-editor>
	  	`;
	}

	get contentElement()
	{
		return this.shadowRoot.querySelector("article[contenteditable]");
	}

	get styloEditor()
	{
		return this.shadowRoot.querySelector("stylo-editor");
	}

	get value()
	{
		return this.contentElement.innerHTML;
	}
}

customElements.define("comment-editor", CommentEditor);
