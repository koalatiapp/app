import { LitElement, html, css } from "lit";
import stylesReset from "../styles-reset.js";
import fontAwesomeImport from "../../utils/fontawesome-import.js";

export class NbSidePanel extends LitElement {
	static get styles()
	{
		return css`
			${stylesReset}
			:host { display: flex; width: 100%; max-width: 500px; flex-direction: column; background-color: var(--color-white); box-shadow: 0 0 3rem rgba(var(--shadow-rgb), .25); position: fixed; top: 0; right: 0; bottom: 0; }

			header { display: flex; justify-content: space-between; align-content: flex-start; gap: 1.5rem; padding: 1.5rem; background-color: var(--color-white); border-bottom: 1px solid var(--color-gray-light); }

			h2 { margin-bottom: 0; }
			.context { font-size: .75rem; color: var(--color-gray-dark); }
			.context:empty { display: none; }

			.content { padding: 1.5rem; overflow: auto; }

			@media (prefers-color-scheme: dark) {

			}
		`;
	}

	static get properties() {
		return {
			title: {type: String},
			context: {type: String},
		};
	}

	constructor()
	{
		super();
		this.title = "";
		this.context = "";
		this.setAttribute("role", "complementary");
		this.setAttribute("aria-labelledby", "sidepanel-title");
	}

	connectedCallback()
	{
		super.connectedCallback();

		this.animateAppearance();

		window.addEventListener("click", (e) => {
			if (!this.contains(e.target)) {
				this.close();
			}
		});
	}

	render()
	{
		return html`
			${fontAwesomeImport}
			<header>
				<div class="heading">
					<h2 id="sidepanel-title">${this.title}</h2>
					<div class="context">${this.context}</div>
				</div>
				<div class="actions">
					<nb-icon-button size="small" color="gray" @click=${this.close}>
						<i class="far fa-times"></i>
					</nb-icon-button>
				</div>
			</header>
			<div class="content">
				<slot></slot>
			</div>
	  	`;
	}

	close()
	{
		this.setAttribute("aria-hidden", true);
		this.animateDisappearance().then(() => this.remove());
	}

	animateAppearance()
	{
		return new Promise(resolve => {
			const animation = this.animate(
				[
					{ transform: "translateX(500px)" },
					{ transform: "translateX(0px)" },
				],
				{
					duration: 350,
					easing: "ease-out",
					iterations: 1
				}
			);

			animation.onfinish = () => {
				resolve();
			};
		});
	}

	animateDisappearance()
	{
		return new Promise(resolve => {
			const animation = this.animate(
				[
					{ transform: "translateX(0px)" },
					{ transform: "translateX(500px)" },
				],
				{
					duration: 350,
					easing: "ease-out",
					iterations: 1
				}
			);

			animation.onfinish = () => resolve();
		});
	}
}

customElements.define("nb-sidepanel", NbSidePanel);
