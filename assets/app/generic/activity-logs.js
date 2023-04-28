import { html, css } from "lit";
import { AbstractDynamicList } from "../abstract-dynamic-list";
import * as timeago from "timeago.js";

export class ActivityLogs extends AbstractDynamicList {
	static get styles()
	{
		return [
			super.styles,
			css`
				.nb--list { gap: 0; }
				.nb--list-header { display: none; }
				.nb--list-item { grid-template-areas: "log"; grid-template-columns: 1fr; border-bottom: 1px solid var(--color-gray-light); border-radius: 0; box-shadow: none; }
				.nb--list-item:last-child { border-bottom: none; }

				p { margin: 0; }
				time { font-size: .75rem; color: var(--color-gray-dark); }
			`
		];
	}

	static get properties() {
		return {
			...super.properties,
			organizationId: {type: String},
			projectId: {type: String},
		};
	}

	static get _columns()
	{
		return [
			{
				key: "log",
				label: "",
				render: (item) => html`
					<p>${Translator.trans(`activity_log.message.${item.type}`, item.data)}</p>
					<time datetime=${item.date_created} title=${item.date_created}>${timeago.format(item.date_created)}</time>
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 100%; line-height: 1em;">&nbsp;</div>
					<div class="nb--list-item-column-placeholder" style="width: 10ch; line-height: .75em;">&nbsp;</div>
				`
			},
		];
	}

	constructor()
	{
		super();
		this.ownerType = null;
		this.organizationId = null;
		this.itemsPerPage = 10;
	}

	supportedEntityType()
	{
		return "ActivityLog";
	}

	fetchListData()
	{
		const parameters = { pagination: true };

		if (this.organizationId) {
			parameters.organization = `/api/organizations/${this.organizationId}`;
		}

		if (this.projectId) {
			parameters.project = `/api/projects/${this.projectId}`;
		}

		return super.fetchListData("/api/activity_logs", parameters);
	}
}

customElements.define("activity-logs", ActivityLogs);
