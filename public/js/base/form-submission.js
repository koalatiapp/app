// Toggle submit button loading state when a Symfony form is submitted
for (const form of document.querySelectorAll("form[method]")) {
	form.addEventListener("submit", () => {
		for (const submitButton of form.querySelectorAll("nb-button[type='submit']")) {
			submitButton.loading = true;
		}
	});
}

