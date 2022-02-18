import getMousePosition from "../get-mouse-position.js";

export class MousewheelPreventionController {
	/** @type {HTMLElement} */
	host;
	#getScrollContainer = () => this.host;

	/**
	 *
	 * @param {HTMLElement} host
	 * @param {function|null} scrollContainerGetter Function that returns the scrollable element. Uses `host` by default.
	 */
	constructor(host, scrollContainerGetter = null) {
		this.host = host;
		host.addController(this);

		if (scrollContainerGetter) {
			this.#getScrollContainer = scrollContainerGetter;
		}
	}

	hostConnected() {
		window.addEventListener("wheel", this.#onWheel.bind(this), { passive: false });
	}

	hostDisconnected()
	{
		window.removeEventListener("wheel", this.#onWheel.bind(this), { passive: false });
	}

	#onWheel(e)
	{
		const scrollContainer = this.#getScrollContainer();
		const mousePos = getMousePosition();
		const hostPos = this.host.getBoundingClientRect();
		const cursorIsInsideElement = hostPos.top <= mousePos.y &&
			hostPos.top + hostPos.height >= mousePos.y &&
			hostPos.left <= mousePos.x &&
			hostPos.left + hostPos.width >= mousePos.x;

		if (cursorIsInsideElement) {
			console.log(e.deltaY, (e.deltaY < 0 && scrollContainer.scrollTop <= 0), (e.deltaY > 0 && scrollContainer.scrollTop + scrollContainer.offsetHeight >= scrollContainer.scrollHeight));
			if ((e.deltaY < 0 && scrollContainer.scrollTop <= 0) ||
				(e.deltaY > 0 && scrollContainer.scrollTop + scrollContainer.offsetHeight >= scrollContainer.scrollHeight)) {
				e.preventDefault();
			}
		}
	}
}
