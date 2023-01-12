import { html, css, render } from "lit";
import { AbstractDynamicList } from "../abstract-dynamic-list";
import fontawesomeImport from "../../utils/fontawesome-import";

export class ChecklistItemList extends AbstractDynamicList {
	static get styles()
	{
		return [
			super.styles,
			css`
				.nb--list-header { display: none; }
				.nb--list-item { grid-template-areas: "checkbox title"; grid-template-columns: 1.5rem 1fr; }

				.nb--list-item-column[nb-column="checkbox"] { display: flex; }
				nb-markdown { display: block; font-size: 1.05em; font-weight: 500; }
				.view-item-details { display: inline-block; margin-right: 1.5ch; font-size: .85em; font-weight: 500; text-align: left; color: var(--color-blue); text-decoration: none; cursor: pointer; transition: all .15s ease; }
				.view-item-details:where(.comments.unresolved) { font-weight: 600; color: var(--color-red); }
				.view-item-details:where(.comments.none) { font-weight: 300; color: var(--color-gray-dark); }
				.view-item-details:hover { color: var(--color-black); }

				@media (prefers-color-scheme: dark) {
					.view-item-details:not(.comments.unresolved) { color: var(--color-blue-dark); }
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
					<nb-checkbox id="checklist-item-${item.id}-checkbox" aria-labelledby="checklist-item-${item.id}-title" ?checked=${item.is_completed} @change=${() => list._toggleItemCompletionCallback(item)}></nb-checkbox>
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
						${list.projectId ? html`
							<a href="#" class="view-item-details comments ${item.unresolved_comment_count ? "unresolved" : (item.comment_count ? "resolved" : "none")}"
								@click=${e => { e.preventDefault(); list._expandChecklistItem(item, false); }}>
								<i class="fad fa-comment"></i>&nbsp;
								${item.unresolved_comment_count
									? Translator.transChoice("project_checklist.item.unresolved_comments_count", item.unresolved_comment_count, { "count": item.unresolved_comment_count })
									: Translator.transChoice("project_checklist.item.comments_count", item.comment_count, { "count": item.comment_count })
								}
							</a>
						`: ""}
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

	supportedEntityType()
	{
		return "ChecklistItem";
	}

	constructor()
	{
		super();
		this.projectId = null;
		this.groupId = null;
		this.itemsPerPage = 25;
	}

	connectedCallback()
	{
		super.connectedCallback();

		// On page load, if an item ID is provided in the hash, open that item's details
		this.addEventListener("items-initialized", () => {
			const urlParams = new URLSearchParams(window.location.hash.substring(1));
			const targetItemId = urlParams.get("item");

			if (!targetItemId) {
				return;
			}

			const targetItem = this.items.find(item => item.id == targetItemId);

			if (!targetItem) {
				return;
			}

			const sidepanel = this._expandChecklistItem(targetItem, false);
			const targetCommentId = urlParams.get("comment");
			const targetThreadId = urlParams.get("thread");

			// If a comment ID is specified, find and show that comment
			if (targetCommentId) {
				const commentList = sidepanel.querySelector("comment-list");

				commentList.addEventListener("comments-initialized", async () => {
					for (const thread of commentList.shadowRoot.querySelectorAll("user-comment")) {
						// If target comment is this thread...
						if (thread.commentId == targetCommentId) {
							thread.classList.add("simulate-focus");
							thread.scrollIntoView({ block: "center" });
							break;
						}
						// If target comment is a reply to this thread...
						if (thread.commentId == targetThreadId) {
							// Ensure the replies are visible
							thread.autoShowReplies = true;

							// Ensure the thread element is rendered
							await thread.updateComplete;

							// Find the comment and scroll to it
							for (const comment of thread.shadowRoot.querySelectorAll("user-comment")) {
								if (comment.commentId == targetCommentId) {
									comment.classList.add("simulate-focus");
									comment.scrollIntoView({ block: "center" });
									return;
								}
							}

							break;
						}
					}
				}, { once: true });
			}
		}, { once: true });
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
		super.fetchListData(`/api/checklist_item_groups/${this.groupId}`, null, "items");
	}

	/**
	 *
	 * @param {Object} item
	 */
	_toggleItemCompletionCallback(item)
	{
		for (const existingItem of this.items) {
			if (existingItem.id == item.id) {
				existingItem.is_completed = !existingItem.is_completed;
				break;
			}
		}

		this.requestUpdate("items");
		this.dispatchEvent(new CustomEvent("checklist-item-toggled", { bubbles: true, composed: true, detail: { item, checked: item.is_completed } }));

		window.plausible("Checklist usage", { props: { action: item.is_completed ? "Checked item" : "Unchecked item" } });
	}

	_expandChecklistItem(item, expandResources)
	{
		const sidepanel = document.createElement("nb-sidepanel");
		sidepanel.title = item.title;
		render(html`
			<nb-markdown>
				<script type="text/markdown">${item.description}</script>
			</nb-markdown>

			${item.resource_urls?.length ? html`
				<nb-accordion ?open=${expandResources}>
					<div slot="summary">${Translator.trans("project_checklist.item.resources")}</div>

					<ul class="grid cols-2" style="--grid-gap: 1rem;">
						${item.resource_urls.map(url => html`
							<li>
								<link-preview-card url=${url}></link-preview-card>
							</li>`
						)}
					</ul>
				</nb-accordion>
			`: ""}

			${this.projectId ? html`
				<hr>
				<h3>${Translator.trans("comment.section_heading")}</h3>
				<br>
				<comment-list projectId=${this.projectId} checklistItemIri=${item["@id"]}></comment-list>
			` : ""}
		`, sidepanel);
		document.body.append(sidepanel);

		this.outlineItems((listItem) => listItem.id == item.id);
		sidepanel.addEventListener("close", () => this.clearOutlines());

		window.plausible("Checklist usage", { props: { action: "Opened item details" } });

		return sidepanel;
	}

	/**
	 * @inheritdoc
	 */
	static get _itemIdentifierCallback()
	{
		return item => {
			return item.id;
		};
	}
}

customElements.define("checklist-item-list", ChecklistItemList);
