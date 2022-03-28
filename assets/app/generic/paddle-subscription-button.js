import { NbButton } from "../../native-bear";
import { ApiClient } from "../../utils/api";
import confirm from "../../utils/confirm.js";

export class PaddleSubscriptionButton extends NbButton {
	static get styles()
	{
		return [
			super.styles
		];
	}

	static get properties() {
		return {
			...super.properties,
			productId: { type: String },
			planName: { type: String },
			actionType: { type: String },
		};
	}

	constructor()
	{
		super();

		this.addEventListener("click", (e) => {
			e.preventDefault();

			if (this.loading) {
				return;
			}

			if (this.actionType == "checkout") {
				this.startCheckoutProcess();
			} else {
				this.startUpdateProcess();
			}
		});
	}

	get _classes()
	{
		return super._classes;
	}

	async startCheckoutProcess()
	{
		this.loading = true;
		const email = await this.#getUserEmail();
		this.triggerPaddleCheckout(email);
	}

	async startUpdateProcess()
	{
		this.loading = true;

		// Request user confirmation before changing subscription plan
		const confirmMessage = Translator.trans("user_settings.subscription.plans.confirm_change", {
			"newPlan": Translator.trans(`plan.${this.planName}.name`)
		});
		confirm(confirmMessage).then(proceed => {
			if (!proceed) {
				this.loading = false;
				return;
			}

			// Make the API call to change the subscription
			ApiClient.post("api_user_subscription_change_plan", { plan: this.planName }).then(() => {
				this.loading = false;
				window.Flash.show("success", "user_settings.subscription.flash.subscription_change_success");
				setTimeout(() => { window.location.reload(); }, 5000);
			});
		});
	}

	#getUserEmail()
	{
		return ApiClient.get("api_user_current").then(response => response.data.email);
	}

	triggerPaddleCheckout(userEmail)
	{
		// eslint-disable-next-line no-undef
		Paddle.Checkout.open({
			allowQuantity: false,
			disableLogout: true,
			email: userEmail,
			displayModeTheme: window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light",
			locale: "en",
			product: this.productId,
			loadCallback: () => { this.loading = false; }
		});
	}
}

customElements.define("paddle-subscription-button", PaddleSubscriptionButton);
