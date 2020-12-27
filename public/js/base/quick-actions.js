/**
 * quick-actions.js
 * Implements the listeners required for the Quick Actions feature to work.
 * The available actions should appear automatically on hover, and disappear shortly after the cursor leaves the area.
 */
(() =>  {
	const quickActions = document.querySelector("#quick-actions");
	let closeTimeout = null;
	let justMouseEntered = false;

	// Auto-show the actions when the cursor enters the area
	quickActions.addEventListener("mouseenter", () => {
		quickActions.classList.add("open");

		// Reset the auto-close timeout, which is set when the cursor leaves the quick actions area
		if (closeTimeout) {
			clearTimeout(closeTimeout);
			closeTimeout = null;
		}

		// Prevents the "hide on click" from being triggered if the actions just auto-appeared
		justMouseEntered = true;
		setTimeout(() => { justMouseEntered = false; }, 200);
	});

	// Auto-hide the actions when the cursor leaves the area
	quickActions.addEventListener("mouseleave", () => {
		closeTimeout = setTimeout(() => {
			quickActions.classList.remove("open");
		}, 250);
	});

	// Auto-hide the actions when the cursor leaves the area
	quickActions.querySelector(".toggle").addEventListener("click", (e) => {
		e.preventDefault();

		if (!justMouseEntered) {
			quickActions.classList.toggle("open");
		}
	});
})();
