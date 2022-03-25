export default function querySelectorAllAnywhere(selector, rootElement = null)
{
	const foundElements = [...document.querySelectorAll(selector)];

	function findWithinElement(element) {
		if (element.shadowRoot || null) {
			for (const childElement of element.shadowRoot.querySelectorAll(":scope > *")) {
				if (childElement.matches(selector)) {
					foundElements.push(childElement);
				}

				findWithinElement(childElement);
			}
		}

		for (const childElement of element.querySelectorAll(":scope > *")) {
			if (childElement.matches(selector)) {
				foundElements.push(childElement);
			}

			findWithinElement(childElement);
		}
	}

	findWithinElement(rootElement || document.documentElement);

	return foundElements;
}


window.querySelectorAllAnywhere = querySelectorAllAnywhere;
