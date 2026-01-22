# Comms API

The Comms API is used for sending and receiving messages between components. For example, when a user is deleted, core sends a message to all components to clean up resources.

## Creating an Event

- Add a new event type in the `bundle/src/Comms/Event` directory extending the `AbstractEvent`.

```php
class GetLicense extends AbstractEvent
{

    public function __construct(
        public array $organizationIds,
        public Component $component
    ) {
    }

    // [] to allow any component to call this
    // or set allowed components manually
    public function from(): array
    {
        return [];
    }

    // only allow receiving from the core
    public function to(): array
    {
        return [Component::CORE];
    }
}
```

- If the message has a response, make sure to add the correct generics:

```php
/**
 * @extends AbstractEvent<GetLicensesResponse>
 */
class GetLicenses extends AbstractEvent
{}
```

## Sending an Event

You can send and event and get its typed response.

```php
use Hyvor\Internal\Bundle\Comms\Comms;

class MyService
{

    public function __construct(
        private Comms $comms,
    ) {}
    
    public function getLicense(): void
    {
        $event = new GetLicenses([1], Component::BLOGS);
        
        // since there is only one to() component, this is always sent there (CORE)
        // otherwise, you can explicitly set to: param
        $response = $this->comms->send($event);
        
        dd($response->license);
    }

}
```

## Receiving and Handling an Event

When an event is sent to a component, internally, the component's /api/comms/event endpoint is called with the serialized payload (see CommsController). After validation, the said event is dispatched.

To handle events, create an event listener, as you would normally do in Symfony:

```php
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\License\GetLicenses;

#[AsEventListener]
class GetLicensesEventListener
{

    public function __invoke(GetLicenses $event)
    {
        // do some processing
        
        // finally, set response
        $event->setResponse(new GetLicensesResponse(...));  
        
        // if something fails, use $event->setError() to set the error
        // message and the HTTP status code
    }

}
```