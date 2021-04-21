import { LitElement, html, css } from "lit-element";
import Modal from "../../../utils/modal.js";

export class RecommendationDetailsLink extends LitElement {
	constructor()
	{
		super();
		this.recommendationId = this.getAttribute("recommendation-id");
	}

	static get styles()
	{
		return css`
			:host { display: inline; }
			a { font-weight: 500; color: var(--color-blue); text-decoration: none; cursor: pointer; transition: all .15s ease; }
			a:hover { color: var(--color-gray-dark); }
		`;
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
			title: Translator.trans("recommendation.modal.title"),
			contentUrl: this.contentUrl,
		});
	}

	_keydownSpacebarHandler(e)
	{
		if (e.code == "Space") {
			this.open(e);
		}
	}

	get contentUrl()
	{
		return Routing.generate("recommendation_group_modal", {recommendationId: this.recommendationId});
	}
}

customElements.define("recommendation-details-link", RecommendationDetailsLink);
