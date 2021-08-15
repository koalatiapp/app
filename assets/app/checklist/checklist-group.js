import { LitElement, html, css } from "lit";
import stylesReset from "../../native-bear/styles-reset.js";
import fontAwesomeImport from "../../utils/fontawesome-import.js";

export class ChecklistGroup extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				.progression { display: inline-block; padding: .35em .75em; margin-left: .35rem; font-size: 0.75rem; font-weight: 500; vertical-align: middle; color: var(--color-blue-dark-faded); background-color: var(--color-blue-10); border-radius: 15px; }
				.completion-indicator { display: inline-block; color: var(--color-green); }
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
			projectId: {type: Number},
			groupId: {type: String},
			itemCount: {type: Number},
			completedItemCount: {type: Number},
		};
	}

	constructor()
	{
		super();
		this.itemCount = 1;
		this.completedItemCount = 0;
		this.previouslyCompleted = false;
	}

	render()
	{
		return html`
			${fontAwesomeImport}
			<div class="group">
				<h2>
					<slot></slot>
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
				<checklist-item-list projectId=${this.projectId} groupId=${this.groupId} @items-initialized=${this._initProgression} @items-updated=${this._updateProgression}></checklist-item-list>
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
	}

	_updateProgression()
	{
		this.itemCount = this.list.items.length;
		this.completedItemCount = this.list.items.filter(item => item.isCompleted).length;

		if (this.itemCount != this.completedItemCount) {
			this.previouslyCompleted = false;
		}
	}
}

customElements.define("checklist-group", ChecklistGroup);
