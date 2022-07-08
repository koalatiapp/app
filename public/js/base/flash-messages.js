class Flash
{
	constructor()
	{
		this.autoDisappearanceDelay = 5000;
		this._initClickToHide();
		this._initAutoDisappearance();
	}

	get wrapper()
	{
		let wrapperNode = document.querySelector("#flash-messages");

		// If the wrapper isn't present in the DOM, create it.
		if (!wrapperNode) {
			wrapperNode = document.createElement("div");
			wrapperNode.id = "flash-messages";
			document.body.prepend(wrapperNode);
		}

		return wrapperNode;
	}

	show(type, message, messageValues = {}, sticky = false)
	{
		const translatedMessage = Translator.trans(message, messageValues);
		const defaultIcon = "fad fa-info-circle";
		const icons = {
			"success": "fad fa-circle-check",
			"danger": "fad fa-times-circle",
			"warning": "fad fa-exclamation-circle",
		};
		const messageNode = document.createElement("div");
		messageNode.className = `flash-message ${type}`;
		messageNode.innerHTML = `
			<span class="icon">
				<i class="${icons[type] || defaultIcon}"></i>
			</span>
			<span class="content">${translatedMessage}</div>
		`;

		if (["warning", "error"].indexOf(type) != -1) {
			messageNode.setAttribute("role", "alert");
		}

		this.wrapper.appendChild(messageNode);

		if (!sticky) {
			this._initAutoDisappearance(messageNode);
		}
	}

	_remove(messageNode)
	{
		if (messageNode.classList.contains("closing")) {
			return;
		}

		messageNode.classList.add("closing");

		const styles = window.getComputedStyle(messageNode);
		const disappearDuration = parseFloat(styles.transitionDuration) * 1000;
		setTimeout(() => { messageNode.remove(); }, disappearDuration);
	}

	_initClickToHide()
	{
		this.wrapper.addEventListener("click", (e) => {
			if (!e.target.matches(".flash-message, .flash-message *")) {
				return;
			}

			e.preventDefault();
			this._remove(e.target.closest(".flash-message"));
		}, true);
	}

	_initAutoDisappearance(specificMessageNode = null)
	{
		if (specificMessageNode !== null) {
			setTimeout(() => { this._remove(specificMessageNode); }, this.autoDisappearanceDelay);
		} else {
			for (const messageNode of this.wrapper.children) {
				setTimeout(() => { this._remove(messageNode); }, this.autoDisappearanceDelay);
			}
		}
	}
}

window.Flash = new Flash();
