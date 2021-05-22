import { LitElement, html, css } from "lit";

export class RecommendationType extends LitElement {
	static get styles()
	{
		return css`
			:host { display: inline; color: var(--color-red); }
			:host([type="ESSENTIAL"]) { color: var(--color-blue-faded); }
			:host([type="OPTIMIZATION"]) { color: var(--color-blue-gray); }
		`;
	}

	static get properties()
	{
		return {
			type: {type: String, attribute: true},
		};
	}

	constructor()
	{
		super();
		this.type = "ISSUE";
	}

	render()
	{
		return html`
			<span>${Translator.trans("recommendation.type." + this.type.toLowerCase())}</span>
	  	`;
	}
}

customElements.define("recommendation-type", RecommendationType);
