import { css } from "lit";
import { NbButton } from "../button/button";

export class NbIconButton extends NbButton {
	static get styles()
	{
		return [
			super.styles,
			css`
				.button { width: 3rem; padding: 11px; font-size: 1.6rem; line-height: 1; }
				.button.small { width: 2.2rem; padding: 11px; font-size: .85rem; }
			`
		];
	}
}

customElements.define("nb-icon-button", NbIconButton);
