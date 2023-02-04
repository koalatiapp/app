import { html, css } from "lit";
import { AbstractDynamicList } from "../abstract-dynamic-list";
import * as timeago from "timeago.js";
import fontawesomeImport from "../../utils/fontawesome-import";

export class ProjectList extends AbstractDynamicList {
	static get styles()
	{
		return [
			super.styles,
			css`
				.nb--list-header,
				.nb--list-item { grid-template-areas: "icon title status createdDate actions"; grid-template-columns: 25px 1fr 12ch 1fr 2ch; }
				.nb--list-item { position: relative; }
				.nb--list-item:hover { position: relative; box-shadow: 0 2px 13px rgba(var(--shadow-rgb), 0.1); }

				.nb--list-item-column[nb-column="icon"] { display: grid; align-content: center; }
				.favicon { object-fit: contain; object-position: center; }
				strong { font-weight: 500; }
				.url { font-size: .75rem; color: var(--color-gray); }
				[nb-column="owner"] { display: none; }
				.nb--list-item-column[nb-column="createdDate"] { color: var(--color-gray-dark); }
				a { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }

				[nb-column="title"] { overflow: hidden; }
				[nb-column="status"] [data-status="NEW"] { color: var(--color-blue-50); }
				[nb-column="status"] [data-status="IN_PROGRESS"] { color: var(--color-blue-80); }
				[nb-column="status"] [data-status="MAINTENANCE"] { color: var(--color-green); }
				[nb-column="status"] [data-status="COMPLETED"] { color: var(--color-gray-dark); }

				/* Hide owner */
				@media (min-width: 768px) {
					:host([show-owners]) .nb--list-header,
					:host([show-owners]) .nb--list-item { grid-template-areas: "icon title status owner createdDate actions"; grid-template-columns: 25px 1fr 12ch 14ch 1fr 2ch; }
					:host([show-owners]) [nb-column="owner"] { display: block; }
					:host([show-owners]) .nb--list-item-column[nb-column="owner"] { white-space: nowrap; text-overflow: ellipsis; color: var(--color-gray-dark); overflow: hidden; }
				}

				@media (max-width: 767px) {
					.nb--list-item { grid-template-areas: "icon title actions"; grid-template-columns: 25px 1fr 2ch; }
					[nb-column="status"],
					[nb-column="owner"],
					[nb-column="createdDate"] { display: none; }
				}
			`
		];
	}

	static get properties() {
		return {
			...super.properties,
			organizationId: {type: String},
		};
	}

	static get _columns()
	{
		return [
			{
				key: "icon",
				label: "",
				render: (item) => html`
					<img src="${item.favicon_url}" loading="lazy" width="25" height="25" class="favicon">
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 25px; line-height: 25px;">&nbsp;</div>
				`
			},
			{
				key: "title",
				label: "project.name",
				render: (item) => html`
					<strong>${item.name}</strong>
					<div class="url">${item.url}</div>
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 15ch;">&nbsp;</div>
					<div class="nb--list-item-column-placeholder" style="width: 25ch; line-height: .75rem;">&nbsp;</div>
				`
			},
			{
				key: "status",
				label: "project.status",
				render: (item) => html`
					<span data-status=${item.status}>
						${Translator.trans("project.status:" + item.status)}
					</span>
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 4ch;">&nbsp;</div>
				`
			},
			{
				key: "owner",
				label: "project.owner.generic",
				render: this._getProjectOwnerName,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 70%;">&nbsp;</div>
				`,
				sortingValue: item => item.date_created
			},
			{
				key: "createdDate",
				label: "project.date_created",
				render: (item) => html`
					${timeago.format(item.date_created)}
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 70%;">&nbsp;</div>
				`,
				sortingValue: item => item.date_created
			},
			{
				key: "actions",
				label: null,
				render: (item) => {
					return html`
						<a href=${Routing.generate("project_dashboard", { id: item.id })} aria-label=${"Open project " + item.name}></a>
						<i class="far fa-angle-right"></i>
					`;
				},
				placeholder: html`

				`
			},
		];
	}

	constructor()
	{
		super();
		this.ownerType = null;
		this.organizationId = null;
		this.sortBy = "createdDate";
		this.sortDirection = "desc";
	}

	supportedEntityType()
	{
		return "Project";
	}

	connectedCallback()
	{
		super.connectedCallback();
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
		if (this.organizationId) {
			return super.fetchListData("/api/projects", { owner_organization: `/api/organizations/${this.organizationId}` });
		}

		return super.fetchListData("/api/projects");
	}

	static _getProjectOwnerName(item)
	{
		return item.owner_organization_name || Translator.trans("generic.you");
	}

	_emptyStateLabel()
	{
		return this.emptyState;
	}
}

customElements.define("project-list", ProjectList);
