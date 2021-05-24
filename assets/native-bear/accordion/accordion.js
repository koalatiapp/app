import { LitElement, html, css } from "lit";

export class NbAccordion extends LitElement {
	static get styles()
	{
		return css`
			:host { display: block; }
			details { margin-top: 10px; font-size: .85rem; background-color: var(--color-gray-lighter); border-radius: 4px; overflow: hidden; }
			summary { display: grid; grid-template-columns: 1fr 3rem; padding: .5rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; background-color: var(--color-gray-lighter); cursor: pointer; transition: background-color .25s ease; }
			summary svg { width: .85rem; height: 100%; margin-right: .5rem; margin-left: auto; color: var(--color-gray); transform: rotate(0deg); transition: transform .25s ease}
			summary:hover { background-color: var(--color-gray-light); }
			::slotted([slot="summary"]) { min-width: 0; max-width: 100%; text-overflow: ellipsis; overflow: hidden; }

			details[open] summary { border-bottom: 1px solid var(--color-gray-light); }
			details[open] summary svg { transform: rotate(-180deg); }
			details[open] .nb--accordion-content { padding: 1rem; }

			@media (prefers-color-scheme: dark) {

			}
		`;
	}

	static get properties() {
		return {
			open: {type: Boolean}
		};
	}

	constructor()
	{
		super();
		this.open = false;
	}

	render()
	{
		return html`
			<details ?open=${this.open}>
				<summary>
					<slot name="summary"></slot>
					<svg aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
						<path fill="currentColor" d="M31.3 192h257.3c17.8 0 26.7 21.5 14.1 34.1L174.1 354.8c-7.8 7.8-20.5 7.8-28.3 0L17.2 226.1C4.6 213.5 13.5 192 31.3 192z"></path>
					</svg>
				</summary>
				<div class="nb--accordion-content">
					<slot @slotchange=${this.handleContentSlotChange}></slot>
				</div>
			</details>
	  	`;
	}

	handleContentSlotChange(e)
	{
		const slottedElements = e.target.assignedElements({flatten: true});

		for (let i = 0; i < slottedElements.length; i++) {
			const element = slottedElements[i];

			if (i == 0) {
				element.style.marginTop = 0;
			}
			if (i == slottedElements.length - 1) {
				element.style.marginBottom = 0;
			}
			if (i > 0 && i < slottedElements.length - 1) {
				element.style.marginTop = "";
				element.style.marginBottom = "";
			}
		}
	}
}

customElements.define("nb-accordion", NbAccordion);
