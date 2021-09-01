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
				.nb--list-item { grid-template-areas: "icon title createdDate actions"; grid-template-columns: 25px 1fr 1fr 2ch; }
				.nb--list-item { position: relative; }
				.nb--list-item:hover { position: relative; box-shadow: 0 2px 13px rgba(var(--shadow-rgb), 0.1); }
				.nb--list-item-column[nb-column="icon"] { display: grid; align-content: center; }
				.favicon { object-fit: contain; object-position: center; }
				strong { font-weight: 500; }
				.url { font-size: .75rem; color: var(--color-gray); }
				.nb--list-item-column[nb-column="createdDate"] { color: var(--color-gray-dark); }
				a { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
			`
		];
	}

	static get properties() {
		return {
			...super.properties,
			organizationId: {type: Number},
			emptyState: {type: String}
		};
	}

	static get _columns()
	{
		return [
			{
				key: "icon",
				label: "",
				render: (item) => html`
					<img src="${item.faviconUrl}" width="25" height="25" class="favicon">
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
				key: "createdDate",
				label: "project.date_created",
				render: (item) => html`
					${timeago.format(item.dateCreated)}
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 70%;">&nbsp;</div>
				`
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
		this.emptyState = Translator.trans("generic.list.empty_state");
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
		super.fetchListData("api_projects_list", { owner_type: this.ownerType, owner_organization_id: this.organizationId });
	}

	_emptyStateLabel()
	{
		return this.emptyState;
	}
}

customElements.define("project-list", ProjectList);
