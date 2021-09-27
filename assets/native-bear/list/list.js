import { LitElement, html, css, unsafeCSS } from "lit";
import {repeat} from "lit/directives/repeat.js";
import stylesReset from "../styles-reset.js";

/**
 * An abstract base for all your listing needs.
 *
 * Every child class should at least redefine the `_columns` static getter.
 *
 * Other methods that can be redefined are:
 * - `_itemIdentifierCallback`
 * - `_emptyStateLabel`
 */
// @TODO: Add documentation for <nb-list> and AbstractDynamicList
export class NbList extends LitElement {
	static get styles()
	{
		return [
			stylesReset,
			css`
				:host { display: block; }
				.nb--list { display: flex; flex-direction: column; gap: 15px; padding: 0; margin: 0; list-style: none; }
				.nb--list-header { display: grid; gap: 20px; padding: 15px; font-size: .85rem; font-weight: 400; color: rgba(0, 0, 0, .57); }
				.nb--list-item { display: grid; gap: 20px; align-items: center; padding: 10px 15px; background-color: var(--color-white); border-radius: 12px; box-shadow: 0 2px 10px rgba(var(--shadow-rgb), 0.05); transition: box-shadow 0.25s ease 0s; }
				.nb--list-item-column { min-width: 0; }
				.nb--list-item-column[nb-column="actions"]:last-child { text-align: right; }
				.nb--list-item-column-placeholder { margin: .15em 0; font-size: .75rem; background-color: #f5f5f5; border-radius: 4px; }
				.nb--list-empty-state { padding: 10px 15px; font-size: 1rem; color: var(--color-gray); }
				.nb--list-pagination { margin-top: 30px; text-align: center; }

				/* Light background mode */
				:host([light]) .nb--list-item { background-color: var(--color-blue-lightest); border: 1px solid var(--color-gray-light); box-shadow: none; }

				@media (prefers-color-scheme: dark) {
					.nb--list-header { color: var(--color-gray); }
					.nb--list-item-column-placeholder { background-color: rgba(255, 255, 255, .05); }
				}
			`,
			this._columns.map(column => css`[nb-column="${unsafeCSS(column.key)}"] { grid-area: ${unsafeCSS(column.key)}; }`)
		];
	}

	static get properties()
	{
		return {
			items: {attribute: false},
			itemsPerPage: {type: Number},
			page: {type: Number},
			sortBy: {type: String},
			sortDirection: {type: String},
			emptyState: {type: String},
			_loading: {state: true},
		};
	}

	/**
	 * A callback that takes in an item and returns its unique identifier.
	 * You should re-implement this getter in your list if items are likely to change during the list's lifetime.
	 * @returns {function|null} Callback that takes in an item and returns its unique identifier.
	 */
	static get _itemIdentifierCallback()
	{
		return null;
	}

	/**
	 * The basic configuration of columns for the list, in the form of an array of objects.
	 *
	 * Here are the properties each column can define:
	 * - `key`: a unique key for the column (used as the column's CSS grid area name)
	 * - `label`: the user-friendly label of the column (used in the list's header)
	 * - `render`: callback used to render the column's conten
	 * - `sortingValue` _(optional)_: callback that returns the raw value of this column if it is to be used for sorting.
	 *
	 * If the `sortingValue` callback is defined for a column, that column will automatically allow sorting (a case-insensitive natural sort is used).
	 */
	static get _columns()
	{
		return [
			{
				key: "name",
				label: "Column name",
				render: (item) => typeof item == "object" ? "Object" : item,
				sortingValue: null,
			}
		];
	}

	constructor()
	{
		super();
		this.items = null;
		this.itemsPerPage = 5;
		this.page = 1;
		this.sortBy = null;
		this.sortDirection = "asc";
		this._searchQuery = null;
		this.emptyState = Translator.trans("generic.list.empty_state");
	}

	render()
	{
		return html`
			${this._renderHeader()}
			<ol class="nb--list">
				${this._itemIdentifierCallback !== null ? repeat(this._pageItems, this.constructor._itemIdentifierCallback, this._renderItem.bind(this)) : this._pageItems.map(this._renderItem)}
			</ol>
			${this.isLoading || this._itemsArray.length ? "" : this._renderEmptyState()}
			${this.isLoading ? this._renderLoadingState() : ""}
			${this._renderPagination()}
		`;
	}

	/**
	 * Enables filtering of items based on the provided search query.
	 * If the search query is empty or null, any existing filter is removed.
	 *
	 * @param {string|null} query The user's search query
	 */
	applySearchQuery(query)
	{
		if (!query || !query.trim().length) {
			query = null;
		}

		this._searchQuery = query;
		this.requestUpdate();
	}

	/**
	 * Clears any existing search filter
	 */
	clearSearchQuery()
	{
		this.applySearchQuery(null);

		return this;
	}

	get isLoading()
	{
		return this.items === null;
	}

	_emptyStateLabel()
	{
		return this.emptyState;
	}

	_renderEmptyState()
	{
		return html`<div class="nb--list-empty-state">
			${this._searchQuery ? Translator.trans("generic.list.no_search_results") : this._emptyStateLabel()}
		</div>`;
	}

	_renderLoadingState()
	{
		const placeholderItem = this._renderPlaceholderItem();
		const placeholderItems = [];

		for (let i = 0; i < this.itemsPerPage; i++) {
			placeholderItems.push(placeholderItem);
		}

		return html`<div class="nb--list nb--list-loading-state">
			${placeholderItems}
		</div>`;
	}

	_renderHeader()
	{
		return html`
			<div class="nb--list-header">
				${this.constructor._columns.map(column => html`<div class="nb--list-header-column" nb-column=${column.key}>${column.label ? Translator.trans(column.label) : ""}</div>`)}
			</div>
		`;
	}

	_renderItem(item)
	{
		const instance = this;

		return html`
			<li class="nb--list-item">
				${this.constructor._columns.map(column => html`<div class="nb--list-item-column" nb-column=${column.key}>${column.render(item, instance)}</div>`)}
			</li>
		`;
	}

	_renderPlaceholderItem()
	{
		return html`
			<li class="nb--list-item nb--list-item-placeholder">
				${this.constructor._columns.map(column => html`<div class="nb--list-item-column" nb-column=${column.key}>${column.placeholder ?? html`<div class="nb--list-item-column-placeholder">&nbsp;</div>`}</div>`)}
			</li>
		`;
	}

	_renderPagination()
	{
		const itemCount = this._itemsArray.length;
		const pageCount = Math.ceil(itemCount / this.itemsPerPage);

		if (pageCount <= 1) {
			return "";
		}

		if (this.page > pageCount + 1) {
			this.page = pageCount + 1;
		}

		return html`
			<div class="nb--list-pagination">
				${[...Array(pageCount).keys()].map(i => html`
					<nb-button size="small" color=${this.page == i + 1 ? "blue" : "gray"} @click=${() => this.page = i + 1}>${i+1}</nb-button>
				`)}
			</div>
		`;
	}

	get _itemsArray()
	{
		const items = this.items !== null ? this.items : [];
		const itemsArray = Array.isArray(items) ? items : Object.values(items);

		return this._filterItems(itemsArray);
	}

	get _pageItems()
	{
		const sortedItems = this._sortItems(this._itemsArray);
		const startIndex = (this.page - 1) * this.itemsPerPage;
		const endIndex = startIndex + this.itemsPerPage;

		return sortedItems.slice(startIndex, endIndex);
	}

	_filterItems(items)
	{
		if (!this._searchQuery) {
			return items;
		}

		const defaultFilterCallback = (item) => {
			let itemString = "";
			for (const value of Object.values(item)) {
				if (["string", "number"].indexOf(typeof value) != -1 && value.toString().toLowerCase().indexOf("koalati") == -1) {
					itemString += value + " ";
				}
			}
			itemString = itemString.trim().toLowerCase();
			const queryString = this._searchQuery.trim().toLowerCase();
			return itemString.indexOf(queryString) != -1;
		};
		const filterCallback = this._filterCallback || defaultFilterCallback;

		return items.filter(filterCallback);
	}

	_sortItems(items)
	{
		const columns = {};
		for (const column of this.constructor._columns) {
			columns[column.key] = column;
		}

		if (this.sortBy === null || !columns[this.sortBy]?.sortingValue) {
			return items;
		}

		return items.sort((a, b) => {
			let valueA = columns[this.sortBy].sortingValue(a);
			let valueB = columns[this.sortBy].sortingValue(b);

			valueA = valueA === null ? "" : valueA.toString();
			valueB = valueB === null ? "" : valueB.toString();

			if (this.sortDirection.toLocaleLowerCase() == "desc") {
				[valueA, valueB] = [valueB, valueA];
			}

			return valueA.localeCompare(valueB, undefined, {numeric: true, sensitivity: "base"});
		});
	}
}
