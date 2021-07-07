import { html, css } from "lit";
import { ApiClient } from "../../../utils/api";
import { AbstractDynamicList } from "../../abstract-dynamic-list";

export class TestingToolList extends AbstractDynamicList {
	static get styles()
	{
		return [
			super.styles,
			css`
				.nb--list-header,
				.nb--list-item { grid-template-areas: "tool actions"; grid-template-columns: 1fr 15ch; }
				.nb--list-item-column[nb-column="tool"] a { display: inline-block; margin-bottom: .15em; font-weight: 600; text-decoration: none; color: var(--color-black); }
				.nb--list-item-column[nb-column="tool"] a:hover { text-decoration: underline; }
				.nb--list-item-column[nb-column="tool"] a i { margin-left: .25em; color: var(--color-blue); opacity: .5; }
				.description { font-size: .8rem; color: var(--color-gray-dark); }


				@media (prefers-color-scheme: dark) {
					.nb--list-item-column[nb-column="tool"] a i { color: var(--color-blue-dark); opacity: 1; }
				}
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
				key: "tool",
				label: "tools.listing.tool",
				render: item => html`
					<a href=${item.tool.url} target="_blank">${item.tool.name} <i class="fad fa-external-link"></i></a>
					<div class="description">${item.tool.description}</div>
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 30%;">&nbsp;</div>
					<div class="nb--list-item-column-placeholder" style="width: 90%; height: .8rem;">&nbsp;</div>
				`
			},
			{
				key: "actions",
				label: null,
				render: (item, list) => html`
					<nb-switch tool-name=${item.tool.name} onLabel=${Translator.trans("tools.listing.enabled")} offLabel=${Translator.trans("tools.listing.disabled")} @change=${e => list.toggleTool(item.tool.name, e.target.checked)} ?checked=${item.enabled} labelFirst></nb-switch>
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
	}

	fetchListData()
	{
		super.fetchListData("api_project_automated_testing_settings_tools_list", { project_id: this.projectId });
	}

	toggleTool(toolName, state)
	{
		ApiClient.post("api_project_automated_testing_settings_tools_toggle", {
			project_id: this.projectId,
			tool: toolName,
			enabled: state ? 1 : 0
		}, null).then(response => {
			this.shadowRoot.querySelector(`nb-switch[tool-name="${response.data.tool}"]`).checked = response.data.enabled;
		});
	}
}

customElements.define("testing-tool-list", TestingToolList);
