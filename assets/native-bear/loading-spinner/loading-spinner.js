import { LitElement, html, css } from "lit";
import stylesReset from "../styles-reset.js";

export class NbLoadingSpinner extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: inline-block; }

				@keyframes spin {
					0% {
						-webkit-transform:rotate(0deg);
						transform:rotate(0deg);
					}
					to {
						-webkit-transform:rotate(1turn);
						transform:rotate(1turn);
					}
				}

				svg { --size: 2rem; width: var(--size); height: var(--size); animation: spin 2s linear infinite; }
				:host([size="small"]) svg { --size: 1rem; }
				:host([size="large"]) svg { --size: 5rem; }

				@media (prefers-color-scheme: dark) {

				}

			`,
		];
	}

	static get properties()
	{
		return {
			size: {type: String},
			color: {type: String},
		};
	}

	constructor()
	{
		super();
		this.size = null;
		this.color = "var(--color-gray)";
	}

	render()
	{
		return html`
			<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
				<path fill=${this.color} d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z"></path>
			</svg>
		`;
	}
}

customElements.define("nb-loading-spinner", NbLoadingSpinner);
