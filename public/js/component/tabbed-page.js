// @TODO: Transform the tabbed page container component into a web-component
for (const tabToggle of document.querySelectorAll(".tabbed-page-wrapper nav a")) {
	tabToggle.addEventListener("click", function(e){
		e.preventDefault();
		tabToggle.closest("ul").querySelector("a.active")?.classList.remove("active");
		tabToggle.classList.add("active");
		tabToggle.closest(".tabbed-page-wrapper").querySelector("main > .active")?.classList.remove("active");
		tabToggle.closest(".tabbed-page-wrapper").querySelector("main > " + tabToggle.getAttribute("href"))?.classList.add("active");
	});
}

// Open tab if defined in URL anchor
const urlAnchor = window.location.hash.replace("#", "");
if (urlAnchor != "") {
	const targetTabToggle = document.querySelector(`.tabbed-page-wrapper nav a[href="#${urlAnchor}"`);
	targetTabToggle?.click();
}
