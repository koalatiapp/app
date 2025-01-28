import { LitElement, html, css } from "lit";
import stylesReset from "../../native-bear/styles-reset.js";

export class UserAvatar extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: inline-block; line-height: 0; }
				img { width: 100%; aspect-ratio: 1/1; object-fit: cover; object-position: center; border-radius: 8px; }
				:host([round]) img { border-radius: 50%; }
			`
		];
	}

	static get properties() {
		return {
			url: {type: String},
			alt: {type: String},
			size: {type: Number},
			round: {type: Boolean},
		};
	}

	constructor()
	{
		super();
		this.url = "";
		this.alt = "";
		this.size = "";
		this.round = false;
	}

	render()
	{
		return html`<img src=${this.standardisedAvatarUrl} alt=${this.alt} loading="lazy" style=${`width: ${this.size ? `${this.size}px` : "100%"}`}>`;
	}

	get standardisedAvatarUrl()
	{
		let url = this.url;

		if (!url.includes("&r=")) {
			url += "&r=g";
		}

		if (!url.includes("&d=")) {
			url += "&d=identicon";
		}

		return url;
	}
}

customElements.define("user-avatar", UserAvatar);
