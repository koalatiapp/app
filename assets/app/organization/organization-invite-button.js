import { html } from "lit";
import { NbButton } from "../../native-bear";
import { ApiClient } from "../../utils/api";
import Modal from "../../utils/modal.js";

export class OrganizationInviteButton extends NbButton {
	static get styles()
	{
		return [
			super.styles
		];
	}

	static get properties() {
		return {
			...super.properties,
			organizationId: { type: String },
			organization: { attribute: false },
		};
	}

	constructor()
	{
		super();
		this._organization = null;
	}

	connectedCallback()
	{
		super.connectedCallback();

		this.addEventListener("click", (e) => {
			e.preventDefault();
			this.openInviteModal();
		});
	}

	get _classes()
	{
		return super._classes;
	}

	getOrganization()
	{
		return new Promise(resolve => {
			if (this._organization) {
				resolve(this._organization);
			}

			ApiClient.get(`/api/organizations/${this.organizationId}`).then(response => {
				this._organization = response;
				resolve(this._organization);
			});
		});
	}

	openInviteModal()
	{
		const modal = new Modal({
			title: Translator.trans("organization.settings.members.invite.modal.title"),
			content: html`
				<form @submit=${e => this._submitInviteCallback(e, modal)}>
					<input type="hidden" name="organization" value=${`/api/organizations/${this.organizationId}`}>
					<fieldset>
						<nb-input type="text" name="first_name" label="${Translator.trans("organization.settings.members.invite.modal.first_name")}" placeholder="John" class="small" required></nb-input>
					</fieldset>
					<fieldset>
						<nb-input type="email" name="email" label="${Translator.trans("organization.settings.members.invite.modal.email")}" placeholder="john.doe@domain.com" class="medium" required></nb-input>
					</fieldset>
					<hr>
					<div class="text-center">
						<nb-button type="submit">${Translator.trans("organization.settings.members.invite.modal.button_label")}</nb-button>
					</div>
				</form>
			`
		});
	}

	_submitInviteCallback(e, modal)
	{
		e.preventDefault();

		modal.toggleLoading();

		const form = e.target;
		const formData = new FormData(form);

		ApiClient.post("/api/organization_members/invite", formData).then(response => {
			if (typeof response == "undefined") {
				return;
			}

			form.reset();
			window.Flash.show("success", Translator.trans("organization.flash.invitation_sent", { name: formData.get("first_name") }));

			window.plausible("Organization usage", { props: { action: "Invited new member" } });
		}).finally(() => {
			modal.toggleLoading(false);
		});
	}
}

customElements.define("organization-invite-button", OrganizationInviteButton);
