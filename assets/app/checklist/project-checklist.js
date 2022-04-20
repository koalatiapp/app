import { LitElement, html } from "lit";
import { ApiClient } from "../../utils/api";
import stylesReset from "../../native-bear/styles-reset.js";

export class ProjectChecklist extends LitElement
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
		};
	}

	constructor()
	{
		super();
		this.groups = [];
		this._loaded = false;
	}

	connectedCallback()
	{
		super.connectedCallback();

		if (this.groups.length == 0) {
			ApiClient.get("api_checklist_group_list", { project_id: this.projectId })
				.then(response => this.groups = response.data);
		}

		this.addEventListener("checklist-item-toggled", function(e) {
			const item = e.detail.item;
			ApiClient.post("api_checklist_item_toggle", { id: item.id, is_completed: item.isCompleted ? 1 : 0 }, null);
		});
	}

	render()
	{
		return html`
			${this.groups.map(group => html`
				<checklist-group projectId=${this.projectId} groupId=${group.id} .items=${group.items}>
					${group.name}
				</checklist-group>
				<hr class="spacer">
			`)}
		`;
	}
}

customElements.define("project-checklist", ProjectChecklist);
