import { fixture, html } from "@open-wc/testing";
import { NbAccordion } from "../../../../assets/native-bear";

const assert = chai.assert;

describe("nb-accordion", () => {
	it("is defined", () => {
		const el = document.createElement("nb-accordion");
		assert.instanceOf(el, NbAccordion);
	});

	it("renders correctly with basic data", async () => {
		const el = await fixture(html`
			<nb-accordion>
				<div slot="summary">Accordion summary</div>
				<div>This is test content that appears in the accordion's content box.</div>
			</nb-accordion>
		`);
		assert.shadowDom.equal(
			el,
			`
				<details>
					<summary>
						<slot name="summary"></slot>
						<svg aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
							<path fill="currentColor" d="M31.3 192h257.3c17.8 0 26.7 21.5 14.1 34.1L174.1 354.8c-7.8 7.8-20.5 7.8-28.3 0L17.2 226.1C4.6 213.5 13.5 192 31.3 192z"></path>
						</svg>
					</summary>
					<div class="nb--accordion-content">
						<slot></slot>
					</div>
				</details>
			`
		);
		assert.equal(
			el.shadowRoot.querySelector("slot[name='summary']").assignedElements()[0].innerHTML,
			"Accordion summary"
		);
		assert.equal(
			el.shadowRoot.querySelector("slot:not([name])").assignedElements()[0].innerHTML,
			"This is test content that appears in the accordion's content box."
		);
	});

	it("renders pre-opened when requested", async () => {
		const el = await fixture(html`<nb-accordion open></nb-accordion>`);
		assert.isTrue(el.shadowRoot.firstElementChild.hasAttribute("open"));
	});
});
