import { html, css } from "lit";
import { AbstractDynamicList } from "../abstract-dynamic-list";
import fontawesomeImport from "../../utils/fontawesome-import";
import { ApiClient } from "../../utils/api";

export class OrganizationMembersList extends AbstractDynamicList {
	static get styles()
	{
		return [
			super.styles,
			css`
				.nb--list-header,
				.nb--list-item { grid-template-areas: "user role actions"; grid-template-columns: 1fr 1fr 14ch; }
			`
		];
	}

	static get properties() {
		return {
			...super.properties,
			organizationId: {type: String}
		};
	}

	static get _columns()
	{
		return [
			{
				key: "user",
				label: "organization.settings.members.list.user",
				render: (item) => html`
					<member-list-item userName=${item.user.firstName + " " + (item.user.lastName || "")} userRole=${item.highestRole} avatarUrl=${item.user.avatarUrl}></member-list-item>
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 8ch;">&nbsp;</div>
				`
			},
			{
				key: "role",
				label: "organization.settings.members.list.role",
				render: (item, list) => html`
					<nb-dropdown reveal-on-hover color="lighter" .options=${{
						"ROLE_ADMIN": Translator.trans("roles.ROLE_ADMIN"),
						"ROLE_MEMBER": Translator.trans("roles.ROLE_MEMBER"),
						"ROLE_VISITOR": Translator.trans("roles.ROLE_VISITOR"),
					}} .eventData=${{id: item.id}} @select=${e => list.updateRoleCallback(e)}>
						<span slot="toggle">${Translator.trans("roles." + item.highestRole)}</span>
					</nb-dropdown>
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 100%;">&nbsp;</div>
				`
			},
			{
				key: "actions",
				label: null,
				render: (item, list) => {
					return html`
						<nb-icon-button title=${Translator.trans("organization.settings.members.list.remove")} size="small" color="danger" @click=${() => list.removeMemberCallback(item)}><i class="fas fa-times"></i></nb-icon-button>
					`;
				},
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 8ch; font-size: 1.75em; margin-left: auto;">&nbsp;</div>
				`
			},
		];
	}

	constructor()
	{
		super();
		this.organizationId = null;
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
		super.fetchListData("api_organization_members_list", { organization_id: this.organizationId });
	}

	updateRoleCallback(e)
	{
		const membershipId = e.detail.id;
		const role = e.detail.value;

		ApiClient.post("api_organization_members_role", { id: membershipId, role }).then(response => {
			if (typeof response == "undefined") {
				const memberListItem = e.detail.dropdown.closest("li").querySelector("member-list-item");
				const currentRole = memberListItem.userRole;
				e.detail.dropdown.slottedToggleElement.innerHTML = Translator.trans("roles." + currentRole);
			} else if (typeof response.data.message != "undefined") {
				window.Flash.show("success", response.data.message);
			}
		});
	}

	removeMemberCallback(item)
	{
		if (confirm(Translator.trans("organization.settings.members.list.remove_confirm", { user: item.user.firstName + " " + item.user.lastName, organization: item.organization.name }))) {
			ApiClient.delete("api_organization_members_delete", { id: item.id }).then(response => {
				if (typeof response != "undefined") {
					this.items = this.items.filter(existingItem => existingItem.id != item.id);
					window.Flash.show("success", response.data.message);
				}
			});
		}
	}
}

customElements.define("organization-members-list", OrganizationMembersList);
