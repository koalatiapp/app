export function escapeHTML(string) {
	const div = document.createElement("div");
	div.innerText = string;
	return div.innerHTML;
}
