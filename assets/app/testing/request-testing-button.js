import { ApiClient } from "../../utils/api";
import { NbButton } from "../../native-bear";
import querySelectorAllAnywhere from "../../utils/query-selector-all-anywhere.js";

export class RequestTestingButton extends NbButton {
	static get styles()
	{
		return super.styles;
	}

	static get properties() {
		return {
			...super.properties,
			projectId: { type: String },
		};
	}

	get _classes()
	{
		return super._classes;
	}


	firstUpdated()
	{
		super.firstUpdated();

		this.addEventListener("click", (e) => {
			e.preventDefault();
			this._submitTestingRequest();
		});
	}

	_submitTestingRequest()
	{
		window.plausible("Testing usage", { props: { action: "Refresh recommendations" } });

		if (this.loading) {
			return;
		}

		for (const refreshButton of querySelectorAllAnywhere("request-testing-button")) {
			refreshButton.loading = true;
		}

		ApiClient.post("/api/testing_requests", { project: `/api/projects/${this.projectId}` }, null)
			.then(() => {
				window.Flash.show("success", Translator.trans("automated_testing.testing_request_created"));

				for (const progressIndicator of querySelectorAllAnywhere("recommendation-progress-indicator")) {
					progressIndicator.reset();
				}
			})
			.catch((error) => {
				console.error(error);
				this.loading = false;
			});
	}
}

customElements.define("request-testing-button", RequestTestingButton);
