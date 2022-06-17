import { LitElement, html, css } from "lit";
import Modal from "../../utils/modal.js";

export class RecommendationDetailsLink extends LitElement {
	static get styles()
	{
		return css`
			:host { display: inline; }
			a { font-weight: 500; color: var(--color-blue); text-decoration: none; cursor: pointer; transition: all .15s ease; }
			a:hover { color: var(--color-gray-darker); }

			@media (prefers-color-scheme: dark) {
				a { color: var(--color-blue-dark); }
				a:hover { color: var(--color-gray); }
			}
		`;
	}

	static get properties() {
		return {
			recommendationId: {type: String}
		};
	}

	constructor()
	{
		super();
		this.recommendationId = null;
		this.recommendationGroup = null;
	}

	render()
	{
		return html`
			<a href="#" role="button" @click=${this.open} @keydown=${this._keydownSpacebarHandler}>
				<slot></slot>
			</a>
	  	`;
	}

	open(e)
	{
		e && e.preventDefault();

		new Modal({
			title: html`${this.recommendationGroup.title.replace(/`(.+?)`/g, "$1")}`,
			size: "large",
			content: html`
				<recommendation-details .recommendationGroup=${this.recommendationGroup} recommendationId=${this.recommendationId}>
			`
		});

		window.plausible("Testing usage", { props: { action: "Open recommendation details" } });
	}

	_keydownSpacebarHandler(e)
	{
		if (e.code == "Space") {
			this.open(e);
		}
	}
}

customElements.define("recommendation-details-link", RecommendationDetailsLink);
