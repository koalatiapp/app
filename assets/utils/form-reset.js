
window.addEventListener("reset", function(e) {
	if (!e.target.matches("form")) {
		return;
	}

	for (const customInput of e.target.querySelectorAll("nb-select, nb-input")) {
		customInput.value = null;
		customInput.input.value = null;
	}

	for (const customRadioList of e.target.querySelectorAll("nb-radio-list")) {
		customRadioList.input.checked = false;
		customRadioList.value = null;
	}
});
