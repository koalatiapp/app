export default function querySelectorAllAnywhere(selector, rootElement = null)
{
	const foundElements = [...document.querySelectorAll(selector)];

	function findWithinElement(element) {
		if (element.shadowRoot || null) {
			for (const childElement of element.shadowRoot.querySelectorAll(selector)) {
				foundElements.push(childElement);
			}
		}

		for (const childElement of element.querySelectorAll(":scope > *")) {
			findWithinElement(childElement);
		}
	}

	findWithinElement(rootElement || document.documentElement);

	return foundElements;
}
