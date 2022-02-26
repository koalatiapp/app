import { html, css, render } from "lit";
import { AbstractDynamicList } from "../abstract-dynamic-list";
import fontawesomeImport from "../../utils/fontawesome-import";
import { ApiClient } from "../../utils/api";

export class ChecklistItemList extends AbstractDynamicList {
	static get styles()
	{
		return [
			super.styles,
			css`
				.nb--list-header { display: none; }
				.nb--list-item { grid-template-areas: "checkbox title"; grid-template-columns: 1.5rem 1fr 2.5rem; }

				.nb--list-item-column[nb-column="checkbox"] { display: flex; }
				nb-markdown { display: block; font-size: 1.05em; font-weight: 500; }
				.view-item-details { display: inline-block; margin-right: 1.5ch; font-size: .85em; font-weight: 500; text-align: left; color: var(--color-blue); text-decoration: none; cursor: pointer; transition: all .15s ease; }
				.view-item-details:where(.comments.unresolved) { font-weight: 600; color: var(--color-red); }
				.view-item-details:where(.comments.none) { font-weight: 300; color: var(--color-gray-dark); }
				.view-item-details:hover { color: var(--color-black); }

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
			projectId: {type: String},
			groupId: {type: String},
		};
	}

	static get _columns()
	{
		return [
			{
				key: "checkbox",
				label: "",
				render: (item, list) => html`
					<nb-checkbox id="checklist-item-${item.id}-checkbox" aria-labelledby="checklist-item-${item.id}-title" ?checked=${item.isCompleted} @change=${() => list._toggleItemCompletionCallback(item)}></nb-checkbox>
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 3ch; height: 3ch;">&nbsp;</div>
				`
			},
			{
				key: "title",
				label: "",
				render: (item, list) => {
					return html`
						<nb-markdown barebones id="checklist-item-${item.id}-title">
							<script type="text/markdown">${item.title}</script>
						</nb-markdown>
						<a href="#" class="view-item-details" @click=${e => { e.preventDefault(); list._expandChecklistItem(item, true); }}>
							<i class="fad fa-circle-info"></i>&nbsp;
							${Translator.trans("project_checklist.item.view_more")}
						</a>
						<a href="#" class="view-item-details comments ${item.unresolvedCommentCount ? "unresolved" : (item.commentCount ? "resolved" : "none")}"
							@click=${e => { e.preventDefault(); list._expandChecklistItem(item, false); }}>
							<i class="fad fa-comment"></i>&nbsp;
							${item.unresolvedCommentCount
								? Translator.transChoice("project_checklist.item.unresolved_comments_count", item.unresolvedCommentCount, { "count": item.unresolvedCommentCount })
								: Translator.transChoice("project_checklist.item.comments_count", item.commentCount, { "count": item.commentCount })
							}
						</a>
					`;
				},
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 90%;">&nbsp;</div>
				`
			},
		];
	}

	supportedDynamicActions()
	{
		return ["update"];
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

		ApiClient.post("api_checklist_item_toggle", { id: item.id, is_completed: item.isCompleted ? 1 : 0 }, null);
	}

	_expandChecklistItem(item, expandResources)
	{
		const sidepanel = document.createElement("nb-sidepanel");
		sidepanel.title = item.title;
		render(html`
			<nb-markdown>
				<script type="text/markdown">${item.description}</script>
			</nb-markdown>

			${item.resourceUrls?.length ? html`
				<nb-accordion ?open=${expandResources}>
					<div slot="summary">${Translator.trans("project_checklist.item.resources")}</div>

					<ul class="grid cols-2" style="--grid-gap: 1rem;">
						${item.resourceUrls.map(url => html`
							<li>
								<link-preview-card url=${url}></link-preview-card>
							</li>`
						)}
					</ul>
				</nb-accordion>
			`: ""}

			<hr>

			<h3>Comments</h3>
			<br>
			<comment-list projectId=${this.projectId} checklistItemId=${item.id}></comment-list>
		`, sidepanel);
		document.body.append(sidepanel);

		this.outlineItems((listItem) => listItem.id == item.id);
		sidepanel.addEventListener("close", () => this.clearOutlines());
	}
}

customElements.define("checklist-item-list", ChecklistItemList);
