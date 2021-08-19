import { fixture, html } from "@open-wc/testing";
import { NbInput } from "../../../../assets/native-bear";

const assert = chai.assert;

describe("nb-input", () => {
	it("is defined", () => {
		const el = document.createElement("nb-input");
		assert.instanceOf(el, NbInput);
	});

	it("renders correctly when standalone", async () => {
		const el = await fixture(html`
			<nb-input inputId="my-input" name="my_input" value="my value">
		`);
		assert.shadowDom.equal(
			el,
			`
				<slot></slot>
				<input type="text" name="my_input" class="input" id="my-input" value="my value" placeholder="" autocomplete="on">
			`
		);
	});

	it("renders correctly with a label", async () => {
		const el = await fixture(html`
			<nb-input inputId="my-input" name="my_input" value="my value" label="My test input">
		`);
		assert.shadowDom.equal(
			el,
			`
				<label for="my-input">My test input</label>
				<slot></slot>
				<input type="text" name="my_input" class="input" id="my-input" value="my value" placeholder="" autocomplete="on">
			`
		);
	});

	it("participates in forms like regular inputs", async () => {
		const formEl = await fixture(html`
			<form>
				<nb-input name="my_input" value="my value">
			</form>
		`);

		const formData = new FormData(formEl);
		assert.equal(formData.get("my_input"), "my value");
	});

	it("doesn't participate in forms when disabled", async () => {
		const formEl = await fixture(html`
			<form>
				<nb-input name="my_input" value="my value" disabled>
			</form>
		`);

		const formData = new FormData(formEl);
		assert.isNull(formData.get("my_input"));
	});
});
