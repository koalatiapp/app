const template = document.createElement("template");
template.innerHTML = `
	<style>
		:host { display: grid; grid-template-columns: 220px minmax(0, 1fr); box-sizing: border-box; }
		* { font-family: inherit; font-size: inherit; box-sizing: inherit; }
		nav { width: calc(100% + 15px); background-color: var(--tabbed-page-navigation-bg-color); border-top-left-radius: 13px; border-bottom-left-radius: 13px; box-shadow: 0 3px 15px 0 rgba(var(--shadow-rgb), .05); }
		nav ul { padding: 25px; padding-right: 40px; margin: 0; list-style: none; position: sticky; top: 0; }
		nav li:not(:first-child) { margin-top: 25px; }
		nav a { display: block; padding: 10px; font-weight: 500; text-decoration: none; color: var(--tabbed-page-navigation-text-color, #eee); -webkit-font-smoothing: antialiased; transition: color .15s ease; }
		nav a:hover { color: var(--tabbed-page-navigation-text-color-hover); }
		nav a.active { font-weight: 600; color: var(--tabbed-page-navigation-text-color-active); }
		main { width: 100%; max-width: 100%; padding: 30px; background-color: var(--tabbed-page-content-bg-color, #fff); border-radius: 13px; box-shadow: 0 3px 15px 0 rgba(var(--shadow-rgb), .05); position: relative; }
		::slotted(*:not(.active)) { display: none; }
	</style>
	<nav>
		<ul>
		</ul>
	</nav>
	<main>
		<slot></slot>
	</main>
`;

export default class TabbedContainer extends HTMLElement
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
		const navigationList = fragment.querySelector("nav ul");
		const defaultSectionId = window.location.hash.replace("#", "");

		// Generate navigation links
		for (const section of this.children) {
			const id = section.id;
			const title = section.querySelector("h2")?.textContent;

			if (!id || !title) {
				continue;
			}

			navigationList.insertAdjacentHTML("beforeend", `
				<li>
					<a href="#${id}" class="${defaultSectionId == id ? "active" : ""}">${title}</a>
				</li>
			`);
			this.contentSections.push(section);
		}

		// Ensure one of the links is marked as active
		if (!navigationList.querySelector("a.active")) {
			navigationList.querySelector("a")?.classList.add("active");
		}

		// Activate the default content section
		const activeSelector = navigationList.querySelector("a.active").getAttribute("href");
		this.querySelector(activeSelector).classList.add("active");

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
			shadowRoot.querySelector("nav a.active")?.classList.remove("active");
			e.target.classList.add("active");

			// Toggle active class on appropriate content sections
			for (const section of contentSections) {
				section.classList.toggle("active", section.id == targetSectionId);
			}
		});
	}
}

// Define and register the component
customElements.define("tabbed-container", TabbedContainer);
