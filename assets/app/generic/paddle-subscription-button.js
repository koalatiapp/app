import { NbButton } from "../../native-bear";
import { ApiClient } from "../../utils/api";

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
		};
	}

	constructor()
	{
		super();

		this.addEventListener("click", (e) => {
			e.preventDefault();
			this.startCheckoutProcess();
		});
	}

	get _classes()
	{
		return super._classes;
	}

	async startCheckoutProcess()
	{
		this.loading = true;
		const email = await this.getUserEmail();
		this.triggerPaddleCheckout(email);
	}

	getUserEmail()
	{
		return ApiClient.get("api_user_current").then(response => response.data.email);
	}

	triggerPaddleCheckout(userEmail)
	{
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
