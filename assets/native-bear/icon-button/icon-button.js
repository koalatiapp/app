import { css } from "lit";
import NbButton from "../button/button";

export default class NbIconButton extends NbButton {
	static get styles()
	{
		return css`
			${super.styles}
			.button.icon-only { width: 3rem; padding: 11px; font-size: 1.6rem; line-height: 1; }
			.button.icon-only.small { width: 2.2rem; padding: 11px; font-size: .85rem; }
		`;
	}

	static get properties() {
		return Object.assign(super.properties, {
			label: {type: String, attribute: true},
		});
	}

	constructor()
	{
		super();
		this.label = "";
	}

	get _classes()
	{
		return super._classes.concat([
			"icon-only"
		]);
	}
}

customElements.define("nb-icon-button", NbIconButton);
