import { html, css } from "lit";
import { ApiClient } from "../../utils/api";
import { AbstractDynamicList } from "../abstract-dynamic-list";

export class ProjectPagesList extends AbstractDynamicList {
	static get styles()
	{
		return [
			super.styles,
			css`
				.nb--list-header,
				.nb--list-item { grid-template-areas: "page actions"; grid-template-columns: 1fr 15ch; }
				.nb--list-item-column[nb-column="page"] .title { display: block; margin-bottom: .15em; font-weight: 600; text-decoration: none; white-space: nowrap; text-overflow: ellipsis; color: var(--color-black); overflow: hidden; }
				.nb--list-item-column[nb-column="page"] a { font-size: .8rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--color-gray-dark); }
				.nb--list-item-column[nb-column="page"] a:hover { text-decoration: underline; color: var(--color-black); }
				.nb--list-item-column[nb-column="tool"] a i { margin-left: .25em; color: var(--color-blue); opacity: .5; }


				@media (prefers-color-scheme: dark) {
					.nb--list-item-column[nb-column="page"] a i { color: var(--color-blue-dark); opacity: 1; }
				}
			`
		];
	}

	static get properties() {
		return {
			...super.properties,
			projectId: {type: String}
		};
	}

	static get _columns()
	{
		return [
			{
				key: "page",
				label: "pages.listing.page",
				render: item => html`
					<div class="title">${item.title}</div>
					<a href=${item.url} target="_blank">${item.url} <i class="fad fa-external-link"></i></a>
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 30%;">&nbsp;</div>
					<div class="nb--list-item-column-placeholder" style="width: 90%; height: .8rem;">&nbsp;</div>
				`,
				sortingValue: (page) => {
					return page.url.replace(/^https?:\/\/(.+)$/, "$1");
				}
			},
			{
				key: "actions",
				label: null,
				render: (item, list) => html`
					<nb-switch page-id=${item.id} onLabel=${Translator.trans("pages.listing.enabled")} offLabel=${Translator.trans("pages.listing.disabled")} @change=${e => list.togglePage(item.id, e.target.checked)} ?checked=${!item.isIgnored} labelFirst></nb-switch>
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 2ch; font-size: 1.75em; margin-left: auto;">&nbsp;</div>
				`
			},
		];
	}

	constructor()
	{
		super();
		this.projectId = null;
		this.itemsPerPage = 10;
		this.sortBy = "page";
		this.sortDirection = "ASC";
	}

	fetchListData()
	{
		super.fetchListData("api_project_pages_list", { project_id: this.projectId });
	}

	togglePage(pageId, state)
	{
		ApiClient.post("api_project_pages_toggle", {
			project_id: this.projectId,
			page_id: pageId,
			enabled: state ? 1 : 0
		}, null).then(response => {
			for (const item of this.items) {
				if (item.id == pageId) {
					item.isIgnored = !response.data.enabled;
				}
			}

			this.requestUpdate("items");
		});
	}
}

customElements.define("project-pages-list", ProjectPagesList);
