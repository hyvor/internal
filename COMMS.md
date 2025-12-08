# Comms API

The Comms API is used for sending and receiving messages between components. For example, when a user is deleted, core sends a message to all components to clean up resources.

## Creating a Message

- Add a new message type in the `bundle/src/Comms/Message` directory.
- Implement the `MessageInterface`.
- If the message has a response, create a corresponding response message as `<MessageName>Response` in the same namespace.

## Sending a Message

```php
use Hyvor\Internal\Bundle\Comms\CommsService;

$message = new YourMessageType($data);
$this->commsService->send($message);
```