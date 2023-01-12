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
			organizationId: {type: String},
			userRole: {type: String}
		};
	}

	static get _columns()
	{
		return [
			{
				key: "user",
				label: "organization.settings.members.list.user",
				render: (item) => html`
					<member-list-item userName=${item.first_name + " " + (item.last_name || "")} userRole=${item.highest_role} avatarUrl=${item.user_avatar}></member-list-item>
				`,
				placeholder: html`
					<div class="nb--list-item-column-placeholder" style="width: 8ch;">&nbsp;</div>
				`
			},
			{
				key: "role",
				label: "organization.settings.members.list.role",
				render: (item, list) => html`
					${item.highest_role == "ROLE_OWNER" ? Translator.trans("roles.ROLE_OWNER") : html`
						<nb-dropdown reveal-on-hover color="lighter" .options=${list.availableRoleOptions}
							.eventData=${{id: item.id, userId: item.user, originalRole: item.highest_role}} @select=${e => list.updateRoleCallback(e)}>
							<span slot="toggle">${Translator.trans("roles." + item.highest_role)}</span>
						</nb-dropdown>
					`}
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
		this.userRole = null;
	}

	supportedEntityType()
	{
		return "OrganizationMember";
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
		super.fetchListData(`/api/organizations/${this.organizationId}/members`);
	}

	updateRoleCallback(e)
	{
		const userId = e.detail.userId;
		const membershipId = e.detail.id;
		const role = e.detail.value;
		const roleValues = {
			"ROLE_OWNER": 1000,
			"ROLE_ADMIN": 100,
			"ROLE_MEMBER": 10,
			"ROLE_VISITOR": 1,
		};
		let membershipItem = null;

		for (const item of this.items) {
			if (item.id == membershipId) {
				membershipItem = item;
				continue;
			}
		}

		(new Promise(resolve => {
			if (userId == CURRENT_USER_ID && roleValues[e.detail.originalRole] > roleValues[role]) {
				return resolve(confirm(Translator.trans("organization.settings.members.list.downgrade_self_confirm")));
			}

			resolve(true);
		})).then(canUpdateRole => {
			if (!canUpdateRole) {
				e.detail.dropdown.slottedToggleElement.innerHTML = Translator.trans("roles." + membershipItem.highestRole);
				return;
			}

			ApiClient.patch(`/api/organization_members/${membershipId}`, { roles: [role] }).then(response => {
				window.Flash.show("success", window.Translator.trans("organization.flash.member_role_updated_successfully", {
					"name": `${response.first_name} ${response.last_name}`,
					"role": window.Translator.trans(`roles.${response.highest_role}`),
				}));

				window.plausible("Organization usage", { props: { action: "Updated member role" } });
			}).catch(error => {
				console.error(error);

				const memberListItem = e.detail.dropdown.closest("li").querySelector("member-list-item");
				const currentRole = memberListItem.userRole;
				e.detail.dropdown.slottedToggleElement.innerHTML = Translator.trans("roles." + currentRole);
			});
		});
	}

	removeMemberCallback(item)
	{
		if (confirm(Translator.trans("organization.settings.members.list.remove_confirm", { user: item.user.firstName + " " + item.user.lastName, organization: item.organization.name }))) {
			ApiClient.delete(`/api/organization_members/${item.id}`).then(response => {
				this.items = this.items.filter(existingItem => existingItem.id != item.id);
				window.Flash.show("success", response.data.message);

				window.plausible("Organization usage", { props: { action: "Removed member" } });
			});
		}
	}

	get availableRoleOptions()
	{
		return {
			"ROLE_ADMIN": Translator.trans("roles.ROLE_ADMIN"),
			"ROLE_MEMBER": Translator.trans("roles.ROLE_MEMBER"),
			"ROLE_VISITOR": Translator.trans("roles.ROLE_VISITOR"),
		};
	}
}

customElements.define("organization-members-list", OrganizationMembersList);
