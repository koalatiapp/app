import { LitElement, html, css } from "lit";
import stylesReset from "../styles-reset.js";

export class NbPagination extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: flex; flew-wrap: wrap; justify-content: center; align-items: center; gap: .5rem; }

				@media (prefers-color-scheme: dark) {

				}
			`
		];
	}

	static get properties() {
		return {
			current: {type: Number},
			pageCount: {type: Number},
		};
	}

	constructor()
	{
		super();
		this._current = 1;
		this.pageCount = 1;
	}

	render()
	{
		if (this.pageCount < 8) {
			return html`
				${[...Array(this.pageCount).keys()].map(i => html`
					<nb-button size="small" color=${this.current == i + 1 ? "blue" : "gray"} @click=${() => this.current = i + 1}>
						${i+1}
					</nb-button>
				`)}
			`;
		} else if (this.current < 4) {
			return html`
				${[...Array(4).keys()].map(i => html`
					<nb-button size="small" color=${this.current == i + 1 ? "blue" : "gray"} @click=${() => this.current = i + 1}>
						${i+1}
					</nb-button>
				`)}
				...
				<nb-button size="small" color="gray" @click=${() => this.current = this.pageCount}>${this.pageCount}</nb-button>
			`;
		} else if (this.current > this.pageCount - 3) {
			return html`
				<nb-button size="small" color="gray" @click=${() => this.current = 1}>1</nb-button>
				...
				${[...Array(4).keys()].map(i => html`
					<nb-button size="small" color=${this.current == this.pageCount - (3 - i) ? "blue" : "gray"} @click=${() => this.current = this.pageCount - (3 - i)}>
						${this.pageCount - (3 - i)}
					</nb-button>
				`)}
			`;
		}

		return html`
			<nb-button size="small" color="gray" @click=${() => this.current = 1}>1</nb-button>
			${this.current > 4 ? "..." : ""}

			${[...Array(2).keys()].map(i => html`
				<nb-button size="small" color="gray" @click=${() => this.current = this.current - 2 + i}>
					${this.current - 2 + i}
				</nb-button>
			`)}

			<nb-button size="small" color="blue">${this.current}</nb-button>

			${[...Array(2).keys()].map(i => html`
				<nb-button size="small" color="gray" @click=${() => this.current = this.current + i + 1}>
					${this.current + i + 1}
				</nb-button>
			`)}

			${this.current < this.pageCount - 3 ? "..." : ""}
			<nb-button size="small" color="gray" @click=${() => this.current = this.pageCount}>${this.pageCount}</nb-button>
		`;
	}

	get current()
	{
		return this._current;
	}

	set current(page)
	{
		const originalPage = this._current;
		page = parseInt(page);

		if (isNaN(page) || !page) {
			page = 1;
		} else if (page > this.pageCount) {
			page = this.pageCount;
		}

		if (page == originalPage) {
			return;
		}

		this._current = page;
		this.requestUpdate("current", originalPage);
		this.dispatchEvent(new CustomEvent("pagination-change", { detail: { page: page }}));
	}
}

customElements.define("nb-pagination", NbPagination);
