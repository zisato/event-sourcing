## Private data

Implement PrivateDataPayloadInterface in event and define the payload private keys
```
class MyEvent extend Event implements PrivateDataPayloadInterface
{
    public function privateDataPayloadKeys(): PayloadKeyCollection
    {
        return PayloadKeyCollection::create(
            PayloadKey::create('foo'),
            PayloadKey::create('nested', 'bar')
        );
    }
}

$payloadKeyCollectionStrategy = new PayloadKeyCollectionByEventInterface();
```

Select service to show/hide payload private data
- crypto key
```
$privateDataPayloadService = new CryptoPrivateDataPayloadService(
    $payloadValueSerializer,
    $secretKeyStore,
    $crypto
);
```
- external repository
```
$privateDataPayloadService = new ExternalPrivateDataPayloadService($privateDataRepository);
```

Decorate event serializer with PrivateDataPayloadEventSerializer
```
$privateDataEventService = PayloadPrivateDataEventService(
    $payloadKeyCollectionStrategy,
    $privateDataPayloadService
);

$newEventSerializer = new PrivateDataPayloadEventSerializer($eventSerializer, $privateDataEventService);
```

Get crazy
```
class CrazyPrivateDataPayloadService implements PrivateDataPayloadServiceInterface
{
    private array $privateDataPayloadServices;

    public function __construct(PrivateDataPayloadServiceInterface ...$privateDataPayloadServices)
    {
        $this->privateDataPayloadServices = $privateDataPayloadServices;
    }

    public function hide(Payload $payload): array
    {
        foreach ($this->privateDataPayloadServices as $privateDataPayloadService) {
            $hiddenPayload = $privateDataPayloadService->hide($payload);

            $payload = Payload::create($payload->aggregateId(), $hiddenPayload, $payload->payloadKeyCollection());
        }

        return $payload->payload();
    }

    public function show(Payload $payload): array
    {
        foreach (array_reverse($this->privateDataPayloadServices) as $privateDataPayloadService) {
            $shownPayload = $privateDataPayloadService->show($payload);

            $payload = Payload::create($payload->aggregateId(), $shownPayload, $payload->payloadKeyCollection());
        }

        return $payload->payload();
    }
}

$cryptoPrivateDataPayloadService = new CryptoPrivateDataPayloadService(
    $payloadValueSerializer,
    $secretKeyStore,
    $crypto
);
$externalPrivateDataPayloadService = new ExternalPrivateDataPayloadService($privateDataRepository);

$privateDataPayloadService = new CrazyPrivateDataPayloadService($cryptoPrivateDataPayloadService, $privateDataPayloadService);
```

On show private data, if ForgottedPrivateDataException is thrown, all the payload private data will be replaced by null and the following metadata key `event_forgotten_values=true` will be added

## Execute tests
```
bin/test.sh
bin/test-coverage.sh
bin/test-xdebug.sh
```

## Execute code tools
```
bin/phpstan.sh
bin/ecs.sh
```