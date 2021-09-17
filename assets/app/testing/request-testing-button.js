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
		for (const refreshButton of querySelectorAllAnywhere("request-testing-button")) {
			refreshButton.loading = true;
		}

		ApiClient.post("api_testing_request_create", { project_id: this.projectId }, null)
			.then(() => {
				window.Flash.show("success", Translator.trans("automated_testing.testing_request_created"));

				for (const progressIndicator of querySelectorAllAnywhere("recommendation-progress-indicator")) {
					progressIndicator._loaded = false;
					progressIndicator._loading = true;

					setTimeout(() => progressIndicator.fetchStatus(), 10000);
				}
			})
			.catch((error) => {
				console.error(error);
				this.loading = false;
			});
	}
}

customElements.define("request-testing-button", RequestTestingButton);
