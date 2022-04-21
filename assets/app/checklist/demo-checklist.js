import { LitElement, html } from "lit";
import stylesReset from "../../native-bear/styles-reset.js";

export class DemoChecklist extends LitElement
{
	static get styles()
	{
		return stylesReset;
	}

	static get properties()
	{
		return {
			projectId: {type: String},
			groups: {type: Array},
			_loaded: {state: true},
		};
	}

	constructor()
	{
		super();
		this.groups = [];
	}

	connectedCallback()
	{
		super.connectedCallback();

		this.#initializeItemGroups();

		this.addEventListener("checklist-item-toggled", (e) => {
			this.#saveStatus(e.detail.item, e.detail.checked);

			// There's no mercure update to trigger the group progress update,
			// so we have to trigger it manually.
			const updatedItemGroup = this.shadowRoot.activeElement;
			const updatedItemList = updatedItemGroup?.shadowRoot?.activeElement;

			if (updatedItemList) {
				updatedItemList.dispatchEvent(new CustomEvent("items-updated"));
			} else {
				// If we couldn't find the affected group... update all of them, just to be safe.
				for (const itemGroup of this.shadowRoot.querySelectorAll("checklist-group")) {
					itemGroup.list.dispatchEvent(new CustomEvent("items-updated"));
				}
			}
		});
	}

	render()
	{
		return html`
			${this.groups.map(group => html`
				<checklist-group .items=${group.items}>
					${group.name}
				</checklist-group>
				<hr class="spacer">
			`)}
		`;
	}

	#initializeItemGroups()
	{
		const jsonTemplate = this.getAttribute("data-template");
		let groupsTemplate = JSON.parse(jsonTemplate);
		groupsTemplate = this.#assignTemporaryIds(groupsTemplate);

		this.groups = this.#updateGroupWithProgress(groupsTemplate);
	}

	#getUserProgress()
	{
		const jsonProgress = localStorage.getItem("demo-checklist-progress") || "{}";

		return JSON.parse(jsonProgress);
	}

	#saveStatus(item, completed)
	{
		const progressObject = this.#getUserProgress();
		progressObject[item.id] = completed;

		localStorage.setItem("demo-checklist-progress", JSON.stringify(progressObject));
	}

	#assignTemporaryIds(groups)
	{
		for (const group of groups) {
			group.id = group.title;

			for (const item of group.items) {
				item.id = item.title;
			}
		}

		return groups;
	}

	#updateGroupWithProgress(groups)
	{
		const progressObject = this.#getUserProgress();

		for (const group of groups) {
			for (const item of group.items) {
				item.isCompleted = progressObject[item.id] ?? false;
			}
		}

		return groups;
	}

	reset()
	{
		localStorage.removeItem("demo-checklist-progress");
		this.insertAdjacentHTML("beforebegin", this.outerHTML);
		this.remove();
	}
}

customElements.define("demo-checklist", DemoChecklist);
