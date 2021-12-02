# Subscriptions

Subscriptions are charged and managed via [Paddle](https://paddle.com/).

The Javascript SDK is used to display subscription buttons in the [`templates/app/user/subscription.html.twig`](templates/app/user/subscription.html.twig) view.

When a user subscribes or changes their existing subscription, Paddle's webhook send a request to the `/webhook/paddle` route, defined in [`src/Controller/Webhook/PaddleController.php`](src/Controller/Webhook/PaddleController.php).


## Subscription change date

When a user downgrades or cancels their subscription, they should still keep their higher plan until the next scheduled payment date, seing as they have already paid for it.

To track this, whenever a user downgrades their plan, the new plan and the date at which the change will take place are stored in the user's entity (via the `UserSubscriptionTrait` trait).

