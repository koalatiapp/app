import { LitElement, html, css } from "lit";
import stylesReset from "../../native-bear/styles-reset.js";
import fontAwesomeImport from "../../utils/fontawesome-import.js";

export class ChecklistGroup extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				.group-toggle { text-decoration: none; color: inherit; transition: color .15s ease; }
				.group-toggle::before { content: '\\f054'; display: inline-block; margin-right: 5px; font-family: var(--fa-style-family); font-size: .7em; font-weight: 400; line-height: 1; transform: rotate(90deg); transform-origin: center; position: relative; top: -1px; transition: transform .25s ease; }
				.group-toggle:hover,
				.group-toggle:hover::before { color: var(--color-blue-dark-faded); }
				.group.loading-items .group-toggle::before { transition: none; }

				.group.closed .group-toggle::before { transform: rotate(0deg); }
				.group.closed checklist-item-list { display: none; }
				.group.closed h2 { margin-bottom: 0; }

				.progression { display: inline-block; padding: .35em .75em; margin-left: .35rem; font-size: 0.75rem; font-weight: 500; vertical-align: middle; color: var(--color-blue-dark-faded); background-color: var(--color-blue-10); border-radius: 15px; }
				.completion-indicator { display: inline-block; margin-left: .35rem; color: var(--color-green); }
				.completion-indicator:not(.previously-completed) { animation: completion-appearance .25s cubic-bezier(0.25, 0.1, 0.37, 2.5) forwards; }

				@keyframes completion-appearance {
					0% { transform: scale(0); }
					100% { transform: scale(1); }
				}

				@media (prefers-color-scheme: dark) {

				}
			`
		];
	}

	static get properties() {
		return {
			projectId: {type: String},
			groupId: {type: String},
			itemCount: {type: Number},
			completedItemCount: {type: Number},
			closed: {type: Boolean},
			_loaded: {state: true},
		};
	}

	constructor()
	{
		super();
		this._loaded = false;
		this.itemCount = 1;
		this.completedItemCount = 0;
		this.previouslyCompleted = false;
		this.closed = false;
	}

	render()
	{
		return html`
			${fontAwesomeImport}
			<div class="group ${this.closed ? "closed" : ""} ${this._loaded ? "" : "loading-items"}">
				<h2>
					<a href="#" class="group-toggle" @click=${(e) => { e.preventDefault(); this.toggle(); }} aria-role="button" aria-expanded=${this.closed ? "false" : "true"} aria-controls="checklist-group-${this.groupId}">
						<slot></slot>
					</a>
					${this.completedItemCount < this.itemCount ? html`
						<span class="progression">
							<span class="completed-count">${this.completedItemCount}</span>
							/
							<span class="total-count">${this.itemCount}</span>
						</span>
					` : html`
						<span class="completion-indicator ${this.previouslyCompleted ? "previously-completed" : ""}">
							<i class="fas fa-circle-check" aria-hidden="true"></i>
						</span>
					`}
				</h2>
				<checklist-item-list projectId=${this.projectId} groupId=${this.groupId} @items-initialized=${this._initProgression} @items-updated=${this._updateProgression} aria-hidden=${this.closed ? "true" : "false"} id="checklist-group-${this.groupId}"></checklist-item-list>
			</div>
	  	`;
	}

	get list()
	{
		return this.shadowRoot.querySelector("checklist-item-list");
	}

	_initProgression()
	{
		this._updateProgression();
		this.previouslyCompleted = this.itemCount == this.completedItemCount;

		setTimeout(() => {
			this._loaded = true;
		}, 10);
	}

	_updateProgression()
	{
		this.itemCount = this.list.items.length;
		this.completedItemCount = this.list.items.filter(item => item.isCompleted).length;

		if (this.itemCount != this.completedItemCount) {
			this.previouslyCompleted = false;
		}

		this.closed = this.completedItemCount >= this.itemCount;
	}

	toggle()
	{
		this.closed = !this.closed;
	}
}

customElements.define("checklist-group", ChecklistGroup);
