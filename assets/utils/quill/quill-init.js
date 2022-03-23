import * as Quill from "quill";

/**
 *
 * @param {string|Element} container
 * @param {object} options
 */
export default function initQuillEditor(container, options = null) {
	if (options === null) {
		options = {
			theme: "snow",
			modules: {
				toolbar: [
					["bold", "italic", "underline"],
					["link", { "list": "bullet" }, { "list": "ordered" }, "blockquote", "image", "video"],
					["code", "code-block"]
				]
			}
		};
	}

	const editor = new Quill(container, options);
	monkeyPatchShadowQuill(editor);

	return editor;
}

/**
 * Fixes Quill selection handling when inside the shadow DOM.
 * Source: https://stackoverflow.com/questions/67914657/quill-editor-inside-shadow-dom
 */
function monkeyPatchShadowQuill(editor) {
	const normalizeNative = (nativeRange) => {
		// document.getSelection model has properties startContainer and endContainer
		// shadow.getSelection model has baseNode and focusNode
		// Unify formats to always look like document.getSelection

		if (nativeRange) {
			const range = nativeRange;

			if (range.baseNode) {
				range.startContainer = nativeRange.baseNode;
				range.endContainer = nativeRange.focusNode;
				range.startOffset = nativeRange.baseOffset;
				range.endOffset = nativeRange.focusOffset;

				if (range.endOffset < range.startOffset) {
					range.startContainer = nativeRange.focusNode;
					range.endContainer = nativeRange.baseNode;
					range.startOffset = nativeRange.focusOffset;
					range.endOffset = nativeRange.baseOffset;
				}
			}

			if (range.startContainer) {

				return {
					start: { node: range.startContainer, offset: range.startOffset },
					end: { node: range.endContainer, offset: range.endOffset },
					native: range
				};
			}
		}

		return null;
	};

	// Hack Quill and replace document.getSelection with shadow.getSelection
	editor.selection.getNativeRange = () => {
		const dom = editor.root.getRootNode();
		const selection = typeof dom.getSelection != "undefined" ? dom.getSelection() : window.getSelection();
		const range = normalizeNative(selection);

		return range;
	};

	// Subscribe to selection change separately,
	// because emitter in Quill doesn't catch this event in Shadow DOM
	document.addEventListener("selectionchange", () => {
		// Update selection and some other properties
		editor.selection.update();
	});
}
