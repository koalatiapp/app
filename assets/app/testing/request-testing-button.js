import { ApiClient } from "../../utils/api";
import { NbButton } from "../../native-bear";

export class RequestTestingButton extends NbButton {
	static get styles()
	{
		return super.styles;
	}

	static get properties() {
		return {
			...super.properties,
			projectId: { type: Number },
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
		this.loading = true;

		ApiClient.post("api_testing_request_create", { project_id: this.projectId }, null)
			.then(() => {
				window.Flash.show("success", Translator.trans("asdsd"));
				setTimeout(() => {
					this.loading = false;
				}, 60000);
			})
			.catch((error) => {
				console.error(error);
				this.loading = false;
			});
	}
}

customElements.define("request-testing-button", RequestTestingButton);
