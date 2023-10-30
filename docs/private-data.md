## Private data

Implement PrivateDataPayloadInterface in event and define the payload private keys
```php
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

Select adapter to show/hide payload private data
- crypto key
```php
$cryptoPayloadEncoderAdapter = new CryptoPayloadEncoderAdapter(
    $payloadValueSerializer,
    $secretKeyStore,
    $crypto
);
```
- external repository
```php
$externalPayloadEncoderAdapter = new ExternalPayloadEncoderAdapter($privateDataRepository);
```

Decorate event serializer with PrivateDataEventSerializer
```php
$privateDataEventService = new PrivateDataEventService(
    $payloadKeyCollectionStrategy,
    $payloadEncoderAdapter
);

$newEventSerializer = new PrivateDataEventSerializer($eventSerializer, $privateDataEventService);
```

Get crazy
```php
class CrazyPayloadEncoderAdapter implements PayloadEncoderAdapterInterface
{
    private array $payloadEncoderAdapters;

    public function __construct(PayloadEncoderAdapterInterface ...$payloadEncoderAdapters)
    {
        $this->payloadEncoderAdapters = $payloadEncoderAdapters;
    }

    
    public function show(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        foreach (array_reverse($this->payloadEncoderAdapters) as $payloadEncoderAdapter) {
            $payload = $payloadEncoderAdapter->show($aggregateId, $payloadKeyCollection, $payload);
        }

        return $payload;
    }

    public function hide(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        foreach ($this->payloadEncoderAdapters as $payloadEncoderAdapter) {
            $payload = $payloadEncoderAdapter->hide($aggregateId, $payloadKeyCollection, $payload);
        }

        return $payload;
    }

    public function forget(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        foreach (array_reverse($this->payloadEncoderAdapters) as $payloadEncoderAdapter) {
            $payload = $payloadEncoderAdapter->forget($aggregateId, $payloadKeyCollection, $payload);
        }

        return $payload;
    }
}

$cryptoPayloadEncoderAdapter = new CryptoPayloadEncoderAdapter(
    $payloadValueSerializer,
    $secretKeyStore,
    $crypto
);
$externalPayloadEncoderAdapter = new ExternalPayloadEncoderAdapter($privateDataRepository);

$crazyPayloadEncoderAdapter = new CrazyPayloadEncoderAdapter($cryptoPayloadEncoderAdapter, $externalPayloadEncoderAdapter);
```

On show private data, if ForgottedPrivateDataException is thrown, all the payload private data will be replaced by null and the following metadata key `event_forgotten_values=true` will be added
