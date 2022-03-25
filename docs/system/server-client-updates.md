# Server to client updates via Mercure

Server-to-client updates are what allow the users to receive updates in real-time,
instead of having to manually refresh the page or request an update. 

This is what powers the collaborative checklist, where you can see updates and 
comments made by other users appear right away on your screen. It also powers the
recommendations module, as well as many other smaller components within the app.

This documentation article is all about theser server-to-client updates: how they
work, how to use them, and the security considerations and system behind them.


## Mercure

[Mercure](https://symfony.com/doc/current/mercure.html) is the technology used on 
Koalati to allow the server to communicate directly with clients. 

If you haven't heard of Mercure before, for basic usage purposes, you can think of it
as one-way websockets: it uses [Server Sent Events](https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events/Using_server-sent_events),
which only allow communication from the server to the client (unlike websockets, which
can communicate both ways).

Although you might have to learn a bit about Mercure to generate environment variables
if you're first setting up your environment, you likely won't have to know much about
Mercure specifically, as Koalati implements an abstraction layer on top of it to make
things a bit easier.


## Sending client updates in PHP

### 1. Creating the entity handler
To send an update for an entity, you first need to make sure an entity handler exists. 
These handlers are defined as classes implementing the `App\Mercure\EntityHandlerInterface`,
and they are located in [`src/Mercure/EntityHandler`](/src/Mercure/EntityHandler).

The main job of the entity handler is to figure out which users should receive updates for
a given entity. This is done by implementing the `getAffectedUsers()` method, which receives
the target entity as its only argument. In general, every user who can view an entity should 
be included in this list of affected users.

For example, the implementation of the `getAffectedUsers()` method for the handler of 
the Project entity should return a list containing:
- every member of the project;
- the owner of the project (if it's owned by a user);
- every member of the organization that owns the project (if it's owned by an organization).

Additionnally, an handler defines two other methods:
- `getSupportedEntity()`, which returns the class of the entity the handler supports.
- `getType()`, which returns a string identifying the type of data this handler supports.
  - Types should be in UpperCamelCase.
  - Ex.: a handler that manages Comments might define its type as `"Comment"`

The type defined by the handler can be used on the client-side to filter which updates
a listener wants to receive and act upon.

### 2. Dispatch an update via the `UpdateDispatcher`

When you create, update or delete an entity or any other instance of a class implementing the 
`App\Mercure\MercureEntityInterface`, use the `App\Mercure\UpdateDispatcher` to dispatch 
Mercure updates to all affected clients. 

The `UpdateDispatcher` can be autowired just like any other service. If you are inside an API 
controller that extends the `AbstractApiController`, the update dispatcher is already autowired
in your controller as the `$updateDispatcher` property.

There are two ways to create and dispatch events: 
- creating and dispatching them right away via `UpdateDispatcher::dispatch()`;
- preparing them with `UpdateDispatcher::prepare()` and dispatching them later with `UpdateDispatcher::dispatchPreparedUpdates()`.

Whether you are using `dispatch()` or `prepare()`, the arguments are the same:
- `$entity` (MercureEntityInterface): the entity or object that the update is about.
- `$type` (string): the type of change ("create", "update", "delete"). Use the `App\Mercure\UpdateType::` constants (ex.: `UpdateType::CREATE`).

In most scenarios, you can use `dispatch()` to create and dispatch the items right away. 

The main use case for `prepare()` is to make the update dispatching flow easier when deleting 
entities (seeing as the updates must be created before an entity is deleted, but should only 
be dispatched once we're sure the deletion has been completed successfully).

Here's a simple example for the usage of the update dispatcher:

```php
use App\Mercure\UpdateDispatcher;
use App\Mercure\UpdateType;

public function resolveComment(UpdateDispatcher $updateDispatcher, CommentRepository $commentRepository): JsonResponse
{
	// Make some kind of change to the entity
	$comment = $commentRepository->find(1);
	$comment->setIsResolved(true);

	// ... 

	// Dispatch a Mercure update of type "UPDATE" to indicate that an existing entity was changed
	$this->updateDispatcher->dispatch($comment, UpdateType::UPDATE);
	
	// ...
}
```


## Listening to updates in Javascript

On every page load, a connection to the server is automatically established by the 
`MercureClient`. 

To listen to updates sent by the server, you must subscribe to the type of event you're
interested in via the `MercureClient.subscribe()` method. 

This method takes two arguments: 
- entityType (`string`): the type of entity for which to listen to events.
- updateCallback (`function`): the callback that will run when an update is received.

The specified entity type must match a type defined by the desired Entity Handler on the 
server-side.

Here's an example of a basic update subscription:

```js
MercureClient.subscribe("Comment", (update) => {
	switch (update.event) {
		case "create":
			console.log("New comment", update.data);
			break;
			
		case "update":
			console.log(`Comment ${update.id} has been updated`, update.data);
			break;

		case "delete":
			console.log(`Comment ${update.id} has been deleted`);
			break;
	}
});
```

### Event data structure

When an event matching the desired type is received, the provided callback will be called
with a single argument containing the update's data, with the following structure:

```js
/**
 * @param {object} update
 * @param {string} update.event Type of event (`create`, `update`, or `delete`)
 * @param {string} update.timestamp Timestamp at which the update was sent.
 * @param {string} update.type Type of entity this update is about.
 * @param {string} update.id ID of the entity this update is about.
 * @param {object} update.data Object representing the entity or the parts of the entity.
 */
```

Here is an example of data one might receive in an update:

```json
{
	"event": "update",
	"timestamp": 1647813342,
	"type": "ChecklistItem",
	"data": {
		"id":"k6jXznvXLq",
		"...": "..."
	},
	"id":"k6jXznvXLq"
}
```
