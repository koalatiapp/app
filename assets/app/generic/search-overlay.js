import { LitElement, html, css } from "lit";
import { ApiClient } from "../../utils/api/index.js";
import stylesReset from "../../native-bear/styles-reset.js";

const resultsCache = {};
let originalFocusTarget;
let controller = new AbortController();

export class SearchOverlay extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				#search-overlay { position: fixed; top: 0; left: 0; display: grid; width: 100%; height: 100%; place-items: center; background-color: rgba(0, 5, 19, .6); backdrop-filter: blur(5px); opacity: 0; pointer-events: none; transition: opacity .35s ease, transform .35s ease; }
				#search-overlay[aria-hidden="false"] { opacity: 1; pointer-events: auto; z-index: 1000; }

				#search-inner { width: 500px; max-width: 90%; }
				#search-inner .search-label { margin-bottom: 15px; font-size: 2rem; font-weight: 700; color: #fff; }
				#search-inner input { padding: 10px 20px; padding-right: 40px; border-width: 3px; border-color: transparent; }
				#search-inner input:focus { border-color: var(--color-blue-light); }
				#search-inner .search-results { height: 150px; margin: 30px 0; overflow: auto; }
				#search-inner .search-results .empty-state { font-weight: 500; color: #fff; }
				#search-inner .search-results a { display: block; width: 100%; padding: 10px; margin-bottom: 10px; color: var(--color-gray-darker); text-decoration: none; background-color: var(--color-white); border-radius: 10px; }
				#search-inner .search-results a .title { font-size: 1.1em; font-weight: 600; }
				#search-inner .search-results a .snippet:not(:empty) { margin-top: 5px; font-size: .9em; font-weight: 500; }
				#search-inner .search-results a.selected { text-decoration: underline; color: var(--color-blue-dark-faded); background-color: var(--color-gray-light); box-shadow: 0 3px 0 var(--color-blue);}
				#search-inner .button-container { text-align: center; }

				@media (prefers-color-scheme: dark) {
					#search-inner .search-results a.selected { color: var(--color-blue-darker); }
				}
			`
		];
	}

	static get properties() {
		return {
			isOpen: {type: Boolean},
			isLoading: {type: Boolean},
		};
	}

	constructor()
	{
		super();
	}

	connectedCallback()
	{
		super.connectedCallback();

		// Register keybinds for the toggling of the seach overlay
		window.addEventListener("keydown", (e) => {
			// Open the search overlay when the Cmd/Ctrl+F/K shortcut is pressed
			if (!this.isOpen && (e.ctrlKey || e.metaKey) && ["f", "k"].indexOf(e.key) != -1) {
				e.preventDefault();
				this.open();
			}
			// Close thee search overlay when the Escape key is pressed
			else if (this.isOpen && e.key == "Escape") {
				e.preventDefault();
				this.close();
			}
		});
	}

	render()
	{
		return html`
			<div id="search-overlay" role="dialog" aria-hidden=${this.isOpen ? "false" : "true"}>
				<div id="search-inner">
					<div class="search-label">${Translator.trans("search.label")}</div>
					<div class="typed-input">
						<nb-input type="text" name="search" placeholder="${Translator.trans("search.placeholder")}" disableAutofill @keydown=${this._resultsNavigationListener} @input=${this._updateSearchResults} @paste=${this._updateSearchResults} @cut=${this._updateSearchResults}>
						<span class="type">
							<i class="far fa-magnifying-glass"></i>
						</span>
					</div>
					<div class="search-results"></div>
					<div class="button-container center">
						<nb-button color="dark" class="cancel" @click=${this.close}>
							${Translator.trans("generic.cancel")}
						</nb-button>
					</div>
				</div>
			</div>
	  	`;
	}

	get resultsWrapper()
	{
		return this.renderRoot.querySelector(".search-results");
	}

	get queryInput()
	{
		return this.renderRoot.querySelector("nb-input[name='search']");
	}

	open()
	{
		originalFocusTarget = document.activeElement;
		this.isOpen = true;
		this.queryInput.focus();
	}

	close()
	{
		this.isOpen = false;
		originalFocusTarget?.focus();
		originalFocusTarget = null;
	}

	_resultsNavigationListener(e)
	{
		if (e.key == "Enter") {
			this.resultsWrapper.querySelector("a.selected")?.click?.();
		} else if (["ArrowDown", "ArrowUp"].indexOf(e.key) != -1) {
			const currentResult = this.resultsWrapper.querySelector("a.selected");

			if (currentResult) {
				const newTarget = e.key == "ArrowUp" ? currentResult.previousElementSibling : currentResult.nextElementSibling;

				if (newTarget) {
					this._selectSearchResult(newTarget);
				}
			}
		}
	}

	/**
	 * Updates the search results based on the search input's current value.
	 */
	async _updateSearchResults() {
		const query = this.queryInput.value.trim();

		if (!query.length) {
			this.resultsWrapper.innerHTML = "";
			return;
		}

		const results = await this._fetchSearchResults(query);

		if (typeof results == "undefined") {
			// Do nothing: the previous request was simply aborted.
		} else if (results.length) {
			const fragment = document.createDocumentFragment();

			for (const result of results) {
				const link = document.createElement("a");
				link.href = result.url;
				link.className = fragment.childElementCount ? "" : "selected";
				// @TODO: Change the string here for a translation message using willdurand/js-translation-bundle (when it starts supporting PHP 8)
				link.innerHTML = `<span class="title">${result.title}</span> - <span class="snippet">${result.snippet || "<i>No description or preview available.</i>"}</span>`;
				link.onfocus = (e) => { this._selectSearchResult(e.target); };
				fragment.appendChild(link);
			}

			this.resultsWrapper.innerHTML = "";
			this.resultsWrapper.appendChild(fragment);
		} else {
			// @TODO: Change the string here for a translation message using willdurand/js-translation-bundle (when it starts supporting PHP 8)
			const emptyState = document.createElement("div");
			emptyState.className = "empty-state";
			emptyState.innerHTML = "No results were found for \"%s\".".replace("%s", "<span class='query'></span>");
			emptyState.querySelector(".query").textContent = query;
			this.resultsWrapper.innerHTML = "";
			this.resultsWrapper.appendChild(emptyState);
		}
	}


	/**
	 * Makes the search call to the server and returns the search results for a given query.
	 * Internally, the results for each query are stored and reused when available.
	 * @param {string} query
	 * @return {Promise<Object[]>}
	 */
	_fetchSearchResults(query)
	{
		// Abort any pending search request
		controller.abort();
		controller = new AbortController();

		// Return a promise, in which the search results will be fetched and returned
		return new Promise((resolve) => {
			// If the results for that query are available in the client-side cache, use them
			if (query in resultsCache) {
				resolve(resultsCache[query]);
				return;
			}

			// Query the server for the search results
			ApiClient.post("api_search", { query }, null, controller).then(response => {
				resultsCache[query] = response.data.results;
				resolve(response.data.results);
			}).catch(() => { resolve(); });
		});
	}

	/**
	 * Sets the provided search result element as selected, unselecting any other previously selected element.
	 * @param {Element} element
	 */
	_selectSearchResult(element)
	{
		this.resultsWrapper.querySelector("a.selected")?.classList.remove("selected");
		element.classList.add("selected");
		element.scrollIntoView({
			block: "center"
		});
	}
}

customElements.define("search-overlay", SearchOverlay);
