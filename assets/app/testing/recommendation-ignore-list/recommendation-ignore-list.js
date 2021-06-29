import escapeHtml  from "escape-html";
import { html, css } from "lit";
import { ApiClient } from "../../../utils/api";
import { AbstractDynamicList } from "../../abstract-dynamic-list";

export class RecommendationIgnoreList extends AbstractDynamicList {
	static get styles()
	{
		return [
			super.styles,
			css`
				.nb--list-header,
				.nb--list-item { grid-template-areas: "title scope tool dateCreated createdBy actions"; grid-template-columns: 1fr 8rem 7.5rem 7rem 5.5rem 4rem; }
				.nb--list-item-column[nb-column="tool"] { font-size: .85rem; }
				.nb--list-item-column[nb-column="test"] { font-size: .85rem; text-align: center; color: var(--color-gray-dark); }

				nb-markdown { display: block; font-weight: 500; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }
			`
		];
	}

	static get properties() {
		return {
			...super.properties,
			projectId: {type: Number}
		};
	}

	static get _columns()
	{
		return [
			{
				key: "title",
				label: "ignore_entry.listing.title",
				render: item => {
					// Remove links from the recommendation title
					const strippedTitle = item.recommendationTitle.replace(/\[.+?\]\(.+?\)/g, "").replace(/\.{2,}/g, ".");

					return html`
						<nb-markdown barebones>
							<script type="text/markdown">${escapeHtml(strippedTitle)}</script>
						</nb-markdown>
					`;
				},
				placeholder: html`<div class="nb--list-item-column-placeholder" style="width: 90%;">&nbsp;</div>`
			},
			{
				key: "scope",
				label: "ignore_entry.listing.scope",
				render: (item) => item.scopeType,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 8ch;">&nbsp;</div>
				`
			},
			{
				key: "tool",
				label: "ignore_entry.listing.tool",
				render: (item) => html`<a href="https://www.npmjs.com/package/${item.tool}" target="_blank" rel="noref noopener">${item.tool}</a>`,
				placeholder: html`<div class="nb--list-item-column-placeholder" style="width: 5ch;">&nbsp;</div>`
			},
			{
				key: "dateCreated",
				label: "ignore_entry.listing.date_created",
				render: (item) => new Intl.DateTimeFormat("en-CA").format(new Date(item.dateCreated)),
				placeholder: html`<div class="nb--list-item-column-placeholder" style="width: 5ch;">&nbsp;</div>`
			},
			{
				key: "createdBy",
				label: "ignore_entry.listing.created_by",
				render: (item) => html`${item.createdBy.firstName} ${item.createdBy.lastName}`,
				placeholder: html`<div class="nb--list-item-column-placeholder" style="width: 5ch; margin: auto;">&nbsp;</div>`
			},
			{
				key: "actions",
				label: null,
				render: (item, list) => html`
					<nb-icon-button size="small" color="gray" @click=${() => list._deleteIgnoreEntry(item)}><i class="fas fa-times"></i></nb-icon-button>
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
	}

	fetchListData()
	{
		super.fetchListData("api_testing_ignore_entry_list", { project_id: this.projectId });
	}

	_deleteIgnoreEntry(item)
	{
		ApiClient.delete("api_testing_ignore_entry_delete", { id: item.id });
	}
}

customElements.define("recommendation-ignore-list", RecommendationIgnoreList);
