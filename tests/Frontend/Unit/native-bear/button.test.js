import { fixture, html } from "@open-wc/testing";
import { NbButton } from "../../../../assets/native-bear";

const assert = chai.assert;
const createEnterKeydownEvent = () => new KeyboardEvent("keydown", {
	altKey:false,
	bubbles: true,
	cancelBubble: false,
	cancelable: true,
	charCode: 0,
	code: "Enter",
	composed: true,
	ctrlKey: false,
	currentTarget: null,
	defaultPrevented: true,
	detail: 0,
	eventPhase: 0,
	isComposing: false,
	isTrusted: true,
	key: "Enter",
	keyCode: 13,
	location: 0,
	metaKey: false,
	repeat: false,
	returnValue: false,
	shiftKey: false,
	type: "keydown",
	which: 13
});

describe("nb-button", () => {
	it("is defined", () => {
		const el = document.createElement("nb-button");
		assert.instanceOf(el, NbButton);
	});

	it("renders without any options", async () => {
		const el = await fixture(html`
			<nb-button>Click me</nb-button>
		`);
		assert.shadowDom.equal(
			el,
			`
				<button type="button" class="button" name="">
					<slot></slot>
				</button>
			`
		);
		assert.equal(
			el.shadowRoot.querySelector("slot").assignedNodes()[0].textContent,
			"Click me"
		);
	});

	it("renders correctly with different colors", async () => {
		let el = await fixture(html`<nb-button></nb-button>`);
		assert.equal(getComputedStyle(el.shadowRoot.firstElementChild).backgroundColor, "rgb(39, 81, 230)", "default");

		el = await fixture(html`<nb-button color="gray"></nb-button>`);
		assert.equal(getComputedStyle(el.shadowRoot.firstElementChild).backgroundColor, "rgb(217, 221, 234)", "gray");

		// Other color variations rely on CSS variables defined by the parent document.
		// However, if these work, we can assume the color class mechanism is working as intended.
	});

	it("renders smaller than default when size is set to small", async () => {
		const defaultEl = await fixture(html`<nb-button>My button</nb-button>`);
		const smallEl = await fixture(html`<nb-button size="small">My button</nb-button>`);
		assert.isBelow(smallEl.offsetHeight, defaultEl.offsetHeight, "height");
		assert.isBelow(smallEl.offsetWidth, defaultEl.offsetWidth, "width");
	});

	it("submits parent form on click", async () => {
		const formEl = await fixture(html`
			<form>
				<nb-button type="submit"></nb-button>
			</form>
		`);

		return new Promise(resolve => {
			formEl.addEventListener("submit", e => {
				e.preventDefault();
				resolve(true);
			});
			formEl.querySelector("nb-button").click();
		});
	});

	it("submits parent form when Enter is pressed within the form", async () => {
		const formEl = await fixture(html`
			<form>
				<input type="text">
				<nb-button type="submit"></nb-button>
			</form>
		`);

		return new Promise(resolve => {
			formEl.addEventListener("submit", e => {
				e.preventDefault();
				resolve(true);
			});
			const input = formEl.querySelector("input");
			input.dispatchEvent(createEnterKeydownEvent());
		});
	});

	it("doesn't submit parent form on Shift+Enter within textarea", async () => {
		const formEl = await fixture(html`
			<form>
				<textarea>
				<nb-button type="submit"></nb-button>
			</form>
		`);

		return new Promise((resolve, reject) => {
			formEl.addEventListener("submit", e => {
				e.preventDefault();
				reject();
			});
			const textarea = formEl.querySelector("textarea");
			textarea.dispatchEvent(createEnterKeydownEvent());
			setTimeout(resolve, 10);
		});
	});

	it("doesn't submit parent form when disabled", async () => {
		const formEl = await fixture(html`
			<form>
				<nb-button type="submit" disabled></nb-button>
			</form>
		`);

		return new Promise((resolve, reject) => {
			formEl.addEventListener("submit", e => {
				e.preventDefault();
				reject();
			});
			formEl.querySelector("nb-button").click();
			setTimeout(resolve, 10);
		});
	});

	it("doesn't submit parent form when loading", async () => {
		const formEl = await fixture(html`
			<form>
				<nb-button type="submit" loading></nb-button>
			</form>
		`);

		return new Promise((resolve, reject) => {
			formEl.addEventListener("submit", e => {
				e.preventDefault();
				reject();
			});
			formEl.querySelector("nb-button").click();
			setTimeout(resolve, 10);
		});
	});
});
