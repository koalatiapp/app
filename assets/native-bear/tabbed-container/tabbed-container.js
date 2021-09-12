const template = document.createElement("template");
template.innerHTML = `
	<style>
		:host { display: grid; grid-template-columns: 220px minmax(0, 1fr); box-sizing: border-box; }
		* { font-family: inherit; font-size: inherit; box-sizing: inherit; }
		nav { width: calc(100% + 15px); background-color: var(--tabbed-page-navigation-bg-color); border-top-left-radius: 13px; border-bottom-left-radius: 13px; box-shadow: 0 3px 15px 0 rgba(var(--shadow-rgb), .05); }
		nav [role='tablist'] { padding: 25px; padding-right: 40px; margin: 0; list-style: none; position: sticky; top: 0; }
		nav a { display: block; padding: 10px; font-weight: 400; text-decoration: none; color: var(--tabbed-page-navigation-text-color, #eee); -webkit-font-smoothing: antialiased; transition: color .15s ease; }
		nav a:not(:first-child) { margin-top: 25px; }
		nav a:hover { color: var(--tabbed-page-navigation-text-color-hover); }
		nav a[aria-selected="true"] { font-weight: 500; color: var(--tabbed-page-navigation-text-color-active); }
		main { width: 100%; max-width: 100%; padding: 30px; background-color: var(--tabbed-page-content-bg-color, #fff); border-radius: 13px; box-shadow: 0 3px 15px 0 rgba(var(--shadow-rgb), .05); position: relative; }
		::slotted([aria-hidden="true"]) { display: none; }

		@media (max-width: 767px) {
			:host { display: flex; flex-direction: column; }
			nav { width: 100%; border-radius: 13px; border-bottom-left-radius: 0; border-bottom-right-radius: 0; }
			nav [role='tablist'] { padding: 5px; }
			nav a { padding: 7px; }
			nav a:not(:first-child) { margin-top: 0; }
			main { width: 100%; padding: 15px; border-radius: 13px; border-top-left-radius: 0; border-top-right-radius: 0; }
		}
	</style>
	<nav>
		<div role="tablist" id="tabbed-container-tablist">
		</div>
	</nav>
	<main>
		<slot></slot>
	</main>
`;

export class TabbedContainer extends HTMLElement
{
	constructor()
	{
		super();

		// Initialize array for content sections added via the slot
		this.contentSections = [];
		this.attachShadow({ mode: "open" });
	}

	connectedCallback()
	{
		// Render the navigation and the overall HTML structure
		// this.contentSections is filled inside this.render()
		this.shadowRoot.appendChild(this.render());

		// Event listeners for tab functionalities
		this.registerListeners();
	}

	render()
	{
		const fragment = template.content.cloneNode(true);
		const navigationList = fragment.querySelector("nav [role='tablist']");
		const defaultSectionId = window.location.hash.replace("#", "");

		// Generate navigation links
		for (const section of this.children) {
			const id = section.id;
			const title = section.querySelector("h2")?.textContent;
			const isActive = defaultSectionId == id;

			if (!id || !title) {
				continue;
			}

			navigationList.insertAdjacentHTML("beforeend", `
				<a href="#${id}" aria-selected="${isActive ? "true" : "false"}" role="tab" aria-controls="${id}">${title}</a
			`);
			section.setAttribute("role", "tabpanel");
			section.setAttribute("aria-hidden", isActive ? "false" : "true");
			section.setAttribute("aria-expanded", isActive ? "true" : "false");
			this.contentSections.push(section);
		}

		// Ensure one of the links is marked as active
		if (!navigationList.querySelector("a[aria-selected='true']")) {
			navigationList.querySelector("a")?.setAttribute("aria-selected", "true");
		}

		// Activate the default content section
		const activeSelector = navigationList.querySelector("a[aria-selected='true']").getAttribute("href");
		const targetSection = this.querySelector(activeSelector);
		targetSection.setAttribute("aria-hidden", "false");
		targetSection.setAttribute("aria-expanded", "true");

		return fragment;
	}

	registerListeners()
	{
		const shadowRoot = this.shadowRoot;
		const contentSections = this.contentSections;

		shadowRoot.querySelector("nav").addEventListener("click", function(e){
			if (!e.target.matches("nav a")) {
				return;
			}

			e.preventDefault();
			const targetSectionId = e.target.getAttribute("href").replace("#", "");

			// Update anchor in URL
			history.replaceState({}, document.title, "#" + targetSectionId);

			// Toggle active class on appropriate nav links
			shadowRoot.querySelector("nav a[aria-selected='true']")?.setAttribute("aria-selected", "false");
			e.target.setAttribute("aria-selected", "true");

			// Toggle active class on appropriate content sections
			for (const section of contentSections) {
				section.setAttribute("aria-hidden", section.id == targetSectionId ? "false" : "true");
				section.setAttribute("aria-expanded", section.id == targetSectionId ? "true" : "false");
			}
		});
	}
}

// Define and register the component
customElements.define("tabbed-container", TabbedContainer);
