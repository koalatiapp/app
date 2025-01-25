import { LitElement, html, css } from "lit";

export class MemberListItem extends LitElement {
	static get styles()
	{
		return css`
			:host { display: grid; grid-template-columns: 2rem 1fr; align-items: center; gap: .75rem; margin-bottom: .75rem; }
			:host(:last-child) { margin-bottom: 0; }
			user-avatar { width: 100%; border-radius: 8px; }
			.name { font-weight: 500; }
			.role { font-size: .85em; color: var(--color-gray-dark); }
		`;
	}

	static get properties()
	{
		return {
			userName: {type: String},
			userRole: {type: String},
			avatarUrl: {type: String},
		};
	}

	render()
	{
		return html`
			<user-avatar url=${this.avatarUrl} size="32"></user-avatar>
			<div class="infos">
				<div class="name">${this.userName}</div>
				<div class="role">${Translator.trans(`roles.${this.userRole}`)}</div>
			</div>
	  	`;
	}
}

customElements.define("member-list-item", MemberListItem);
