export default function getActiveElement()
{
	let element = document.activeElement;

	while (element && element.shadowRoot) {
		element = element.shadowRoot.activeElement;
	}

	return element;
}
