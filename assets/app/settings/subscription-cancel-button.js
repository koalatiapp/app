import { NbButton } from "../../native-bear";
import { ApiClient } from "../../utils/api";
import confirm from "../../utils/confirm.js";

export class SusbcriptionCancelButton extends NbButton {
	connectedCallback()
	{
		super.connectedCallback();

		this.addEventListener("click", async (e) => {
			e.preventDefault();

			if (this.loading) {
				return;
			}

			const userConfirmed = await confirm(
				Translator.trans("user_settings.subscription.overview.cancellation_confirm_prompt"),
				Translator.trans("user_settings.subscription.overview.cancellation_confirm_button"),
				null,
				"danger"
			);

			if (userConfirmed) {
				this.loading = true;

				ApiClient.post("api_user_subscription_cancel_plan")
					.then(response => {
						if (response.status == "ok") {
							window.Flash.show("success", "user_settings.subscription.flash.subscription_cancellation_success");

							setTimeout(() => { window.location.reload(); }, 5000);
						}
					})
					.catch(() => {
						this.loading = false;
					});
			}
		});
	}

	get _classes()
	{
		return super._classes;
	}
}

customElements.define("subscription-cancel-button", SusbcriptionCancelButton);
