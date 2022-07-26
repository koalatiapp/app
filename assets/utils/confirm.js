import { html } from "lit";
import Modal from "./modal.js";

/**
 *
 * @param {string|object|Promise<string|object>} content Content of the confirmation modal. We suggest you use Lit's `html` template literal if you require any HTML structure.
 * @param {string|null} confirmLabel Label of the confirm button. If null, a default "confirm" label will be displayed.
 * @param {string|null} cancelLabel Label of the cancel button. If null, a default "cancel" label will be displayed.
 * @param {string|null} confirmColor Color of the cancel button. If null, "blue" is used.
 *
 * @returns Promise<bool>
 */
export default function confirm(content, confirmLabel = null, cancelLabel = null, confirmColor = null) {
	if (typeof content == "string") {
		content = html`<p>${content}</p>`;
	}

	return new Promise(resolve => {
		const modalContent = html`
			<div class="confirm-body">
				${content}
			</div>
			<div class="button-container small right">
				<nb-button color="gray" data-action="cancel" size="small">${cancelLabel ? cancelLabel : Translator.trans("generic.cancel")}</nb-button>
				<nb-button color="${confirmColor || "blue"}" data-action="confirm" size="small">${confirmLabel ? confirmLabel : Translator.trans("generic.confirm")}</nb-button>
			</div>
		`;

		const modal = new Modal({
			content: modalContent,
			size: "confirm",
			allowClosing: false,
		});

		modal.contentElement.querySelector("[data-action='confirm']").addEventListener("click", () => {
			resolve(true);
			modal.close(true);
		});

		modal.contentElement.querySelector("[data-action='cancel']").addEventListener("click", () => {
			resolve(false);
			modal.close(true);
		});
	});
}
