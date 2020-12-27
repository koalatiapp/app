/**
 * search-overlay.js
 * Implements the listeners & features for the Search Overlay.
 * The available actions should appear automatically on hover, and disappear shortly after the cursor leaves the area.
 */
(() =>  {
	const searchOverlay = document.querySelector("#search-overlay");
	const searchInput = searchOverlay.querySelector("input");
	const searchResultWrapper = searchOverlay.querySelector(".search-results");
	const searchToggle = document.querySelector("#search-toggle");
	const resultsCache = {};
	const controller = new AbortController();
	let searchOverlayIsOpen = false;
	let originalFocusTarget = null;

	// Open search overlay when clicking on the top bar's search toggle
	searchToggle.addEventListener("click", (e) => {
		e.preventDefault();
		openSearchOverlay();
	});

	// Register keybinds for the toggling of the seach overlay
	window.addEventListener("keydown", (e) => {
		// Open the search overlay when the Cmd/Ctrl+F/K shortcut is pressed
		if (!searchOverlayIsOpen && (e.ctrlKey || e.metaKey) && ["f", "k"].indexOf(e.key) != -1) {
			e.preventDefault();
			openSearchOverlay();
		}
		// Close thee search overlay when the Escape key is pressed
		else if (searchOverlayIsOpen && e.key == "Escape") {
			e.preventDefault();
			closeSearchOverlay();
		}
	});

	// Close the search overlay when its Cancel button is clicked
	searchOverlay.querySelector(".cancel").addEventListener("click", closeSearchOverlay);

	// Utility function to open the search overlay
	function openSearchOverlay() {
		originalFocusTarget = document.activeElement;
		searchOverlay.classList.add("open");
		searchInput.focus();
		searchOverlayIsOpen = true;
	}

	// Utility function to open the search overlay
	function closeSearchOverlay() {
		searchOverlay.classList.remove("open");
		searchOverlayIsOpen = false;
		originalFocusTarget?.focus();
		originalFocusTarget = null;
	}

	// Update the seach results when the search input's value is changed
	searchInput.addEventListener("input", updateSearchResults);
	searchInput.addEventListener("cut", updateSearchResults);
	searchInput.addEventListener("paste", updateSearchResults);

	// Initialze the keybinds for results navigation and selection
	searchInput.addEventListener("keydown", (e) => {
		if (e.key == "Enter") {
			searchResultWrapper.querySelector("a.selected")?.click?.();
		} else if (["ArrowDown", "ArrowUp"].indexOf(e.key) != -1) {
			const currentResult = searchResultWrapper.querySelector("a.selected");

			if (currentResult) {
				const newTarget = e.key == "ArrowUp" ? currentResult.previousElementSibling : currentResult.nextElementSibling;

				if (newTarget) {
					selectSearchResult(newTarget);
				}
			}
		}
	});

	/**
	 * Updates the search results based on the search input's current value.
	 */
	async function updateSearchResults() {
		const query = searchInput.value.trim();

		if (!query.length) {
			searchResultWrapper.innerHTML = "";
			return;
		}

		const results = await fetchSearchResults(query);
		searchResultWrapper.innerHTML = "";

		if (results.length) {
			const fragment = document.createDocumentFragment();

			for (const result of results) {
				const link = document.createElement("a");
				link.href = result.url;
				link.className = fragment.childElementCount ? "" : "selected";
				link.innerHTML = `<span class="title">${result.title}</span> - <span class="snippet">${result.snippet || ""}</span>`;
				link.onfocus = (e) => { selectSearchResult(e.target); };
				fragment.appendChild(link);
			}

			searchResultWrapper.appendChild(fragment);
		} else {
			// @TODO: Change the string here for a translation message using willdurand/js-translation-bundle (when it starts supporting PHP 8)
			const emptyState = document.createElement("div");
			emptyState.className = "empty-state";
			emptyState.innerHTML = "No results were found for \"%s\".".replace("%s", "<span class='query'></span>");
			emptyState.querySelector(".query").textContent = query;
			searchResultWrapper.appendChild(emptyState);
		}
	}

	/**
	 * Makes the search call to the server and returns the search results for a given query.
	 * Internally, the results for each query are stored and reused when available.
	 * @param {string} query
	 * @return {Promise<Object[]>}
	 */
	function fetchSearchResults(query) {
		// Abort any pending search request
		controller.abort();

		// Return a promise, in which the search results will be fetched and returned
		return new Promise((resolve) => {
			// If the results for that query are available in the client-side cache, use them
			if (query in resultsCache) {
				resolve(resultsCache[query]);
				return;
			}

			// Query the server for the search results
			wretch(Routing.generate("json_search"))
				.signal(controller)
				.formUrl({ query })
				.post()
				.json()
				.then(response => {
					resultsCache[query] = response.results;
					resolve(response.results);
				})
				.catch(() => { resolve([]); });
		});
	}

	/**
	 * Sets the provided search result element as selected, unselecting any other previously selected element.
	 * @param {Element} element
	 */
	function selectSearchResult(element) {
		searchResultWrapper.querySelector("a.selected")?.classList.remove("selected");
		element.classList.add("selected");
	}
})();
