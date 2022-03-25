/**
 * Detects whether the target element is contained by the parentElement,
 * even if it is inside the shadow DOM of a child element.
 *
 * @param {HTMLElement} parentElement
 * @param {HTMLElement} targetElement
 */
export default function elementContains(parentElement, targetElement)
{
	if (parentElement.contains(targetElement)) {
		return true;
	}

	let targetParentRoot;

	do {
		targetParentRoot = targetElement.getRootNode();

		if (targetParentRoot != document && parentElement.contains(targetParentRoot.host)) {
			return true;
		}

		targetElement = targetParentRoot.host ?? targetParentRoot;
	} while (targetParentRoot != document);

	return false;
}
