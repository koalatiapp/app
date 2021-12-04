# Subscriptions

Subscriptions are charged and managed via [Paddle](https://paddle.com/).

The Javascript SDK is used to display subscription buttons in the [`templates/app/user/subscription.html.twig`](templates/app/user/subscription.html.twig) view.

When a user subscribes or changes their existing subscription, Paddle's webhook send a request to the `/webhook/paddle` route, defined in [`src/Controller/Webhook/PaddleController.php`](src/Controller/Webhook/PaddleController.php).


## Subscription change date

When a user downgrades or cancels their subscription, they should still keep their higher plan until the next scheduled payment date, seing as they have already paid for it.

To track this, whenever a user downgrades their plan, the new plan and the date at which the change will take place are stored in the user's entity (via the `UserSubscriptionTrait` trait).


## How plan-based limitations are implemented

### Restricted features
Features that are only usable by certain plans are implemented in different ways, depending on the context.

In every case, a [security voter](https://symfony.com/doc/current/security/voters.html) is used to check if the user has access to the feature/resource.

Here's how denials are handled in different contexts when users attempt to use features they aren't allowed to use on their current plan.

#### For user-facing controller routes
The [`SuggestUpgradeControllerTrait`](/src/Controller/Trait/SuggestUpgradeControllerTrait.php)'s `suggestPlanUpgrade(reason)` method is used in the controller to redirect them to the Subscriptions settings page, with a message suggesting they upgrade to use this feature.

#### For requests to the internal API
TODO: The internal API returns a 403 response code with a special message, which is detected by the `ApiClient`. The `ApiClient` then displays a temporary notice on the page to let the user know this feature isn't available on their current plan, and that they should upgrade if they need to use it.

#### For message handlers
In case a message makes its way through without the user having adequate permissions with their current plan, the handler should simply end the processing of that message without any further feedback.


### Quotas
At the moment, the only quota is that of the active projects per month. 

This quota is actually a soft-limit: the users are not stopped from going over their quota, but they will receive a notice that they are over their quota and should upgrade their plan if they plan to keep exceeding it. 

This check/notice is handled by the [QuotaManager](/src/Subscription/QuotaManager.php)'s `notifyIfQuotaExceeded(Project $project)` method, which should be called whenever an action could have increased the number of active projects for a user.

TODO: Implement either a hard limit for quotas, or a way to easily monitor and handle users who always go over their quota.
