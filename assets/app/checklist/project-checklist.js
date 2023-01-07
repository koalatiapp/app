import { LitElement, html, css } from "lit";
import { ApiClient } from "../../utils/api";
import stylesReset from "../../native-bear/styles-reset.js";

export class ProjectChecklist extends LitElement
{
	static get styles()
	{
		return [
			stylesReset,
			css`
				checklist-group[hidden] + .spacer { display: none; }
			`
		];
	}

	static get properties()
	{
		return {
			projectId: {type: String},
			groups: {type: Array},
			filter: {type: String}
		};
	}

	constructor()
	{
		super();
		this.groups = [];
		this.filter = null;
		this._loaded = false;
	}

	connectedCallback()
	{
		super.connectedCallback();

		if (this.groups.length == 0) {
			ApiClient.get(`/api/projects/${this.projectId}/checklist`)
				.then(response => this.groups = response.item_groups);
		}

		this.addEventListener("checklist-item-toggled", function(e) {
			const item = e.detail.item;
			ApiClient.patch(item["@id"], { is_completed: item.is_completed }, null);
		});
	}

	render()
	{
		return html`
			${this.groups.map(group => html`
				<checklist-group projectId=${this.projectId} groupId=${group.id} filter=${this.filter} .items=${group.items}>
					${group.name}
				</checklist-group>
				<hr class="spacer">
			`)}
		`;
	}

	firstUpdated(changedProperties)
	{
		if (changedProperties.has("filter")) {
			this.updateComplete.then(() => {
				return new Promise(resolve => {
					const waitForGroupsInitializationInternal = setInterval(() => {
						if (Object.values(this.groups).length > 0) {
							clearInterval(waitForGroupsInitializationInternal);
							resolve();
						}
					}, 50);
				});
			}).then(() => {
				this._updateGroupsVisibility();
			});
		}
	}

	updated(changedProperties)
	{
		if (changedProperties.has("filter")) {
			this._updateGroupsVisibility();
		}
	}

	/**
	 * Automatically opens groups with visible items, and closes groups
	 * with no visible items.
	 */
	_updateGroupsVisibility()
	{
		const hasFilter = !!this.filter;

		for (const group of this.shadowRoot.querySelectorAll("checklist-group")) {
			group.updateComplete.then(() => {
				if (hasFilter) {
					group.closed = group?.list.visibleItemCount == 0;
				} else {
					group.closed = group.completedItemCount >= group.itemCount;
				}
			});
		}
	}
}

customElements.define("project-checklist", ProjectChecklist);
