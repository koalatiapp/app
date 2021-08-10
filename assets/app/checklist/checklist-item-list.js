import { html, css } from "lit";
import { AbstractDynamicList } from "../abstract-dynamic-list";
import Modal from "../../utils/modal";
import fontawesomeImport from "../../utils/fontawesome-import";
import { ApiClient } from "../../utils/api";

export class ChecklistItemList extends AbstractDynamicList {
	static get styles()
	{
		return [
			super.styles,
			css`
				.nb--list-header { display: none; }
				.nb--list-item { grid-template-areas: "checkbox title"; grid-template-columns: 2rem 1fr; }

				nb-markdown { display: block; font-weight: 500; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; cursor: pointer; }
				.view-item-details { font-size: .85em; font-weight: 500; color: var(--color-blue); text-decoration: none; cursor: pointer; transition: all .15s ease; }
				.view-item-details:hover { fcolor: var(--color-gray-darker);}

				@media (prefers-color-scheme: dark) {
					.view-item-details { color: var(--color-blue-dark); }
					.view-item-details:hover { color: var(--color-gray); }
				}
			`
		];
	}

	static get properties() {
		return {
			...super.properties,
			projectId: {type: Number},
			groupId: {type: Number},
		};
	}

	static get _columns()
	{
		return [
			{
				key: "checkbox",
				label: "",
				render: (item, list) => html`
					<input type="checkbox" id="checklist-item-${item.id}-checkbox" aria-labelledby="checklist-item-${item.id}-title" ?checked=${item.isCompleted} @change=${() => list._toggleItemCompletionCallback(item)}>
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 3ch; height: 3ch;">&nbsp;</div>
				`
			},
			{
				key: "title",
				label: "checklist.listing.title",
				render: (item, list) => {
					return html`
						<nb-markdown barebones id="checklist-item-${item.id}-title" @click=${() => list.shadowRoot.querySelector(`#checklist-item-${item.id}-checkbox`).click()}>
							<script type="text/markdown">${item.title}</script>
						</nb-markdown>
						<a href="#" class="view-item-details" @click=${e => { e.preventDefault(); list._showItemDetailsCallback(item); }}>
							<i class="fad fa-circle-info"></i>&nbsp;
							${Translator.trans("checklist.item.view_more")}
						</a>
					`;
				},
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 90%;">&nbsp;</div>
					<div class="nb--list-item-column-placeholder" style="width: min(30ch, 75%); font-size: .65em;">&nbsp;</div>
				`
			},
		];
	}

	constructor()
	{
		super();
		this.projectId = null;
		this.groupId = null;
		this.itemsPerPage = 25;
	}

	render()
	{
		return [
			fontawesomeImport,
			super.render()
		];
	}

	fetchListData()
	{
		super.fetchListData("api_checklist_item_list", { project_id: this.projectId, group_id: this.groupId });
	}

	/**
	 *
	 * @param {Object} item
	 */
	_toggleItemCompletionCallback(item)
	{
		for (const existingItem of this.items) {
			if (existingItem.id == item.id) {
				existingItem.isCompleted = !existingItem.isCompleted;
				break;
			}
		}
		this.requestUpdate("items");

		ApiClient.post("api_checklist_item_toggle", { id: item.id, is_completed: item.isCompleted }, null);
	}

	_showItemDetailsCallback(item)
	{
		new Modal({
			title: html`
				<nb-markdown barebones>
					<script type="text/markdown">${item.title}</script>
				</nb-markdown>
			`,
			content: html`
				<nb-markdown barebones>
					<script type="text/markdown">${item.description}</script>
				</nb-markdown>
				<hr>
				<ul>
					${item.resourceUrls?.length ? item.resourceUrls.map(url => html`
						<li>
							<a href=${url} target="_blank">${url}</a>
						</li>`
					) : ""}
				</ul>
			`
		});
	}
}

customElements.define("checklist-item-list", ChecklistItemList);
