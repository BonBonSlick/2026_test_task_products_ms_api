# Quick Reference Guide

## Files Modified/Created

### Created Files
1. ✅ `src/Application/CQRS/Command/UseCase/CreateProduct.php` - Command class
2. ✅ `src/Application/CQRS/Command/Handler/CreateProductHandler.php` - Command handler
3. ✅ `src/Domain/Model/Product/Event/ProductCreated.php` - Domain event
4. ✅ `MESSAGE_BUS_ARCHITECTURE.md` - Architecture documentation

### Modified Files
1. ✅ `config/packages/messenger.yaml` - Two buses + routing configuration
2. ✅ `src/Application/CQRS/Query/Handler/GetProductListHandler.php` - Completed query handler
3. ✅ `src/Application/Controller/ProductController.php` - Uses both buses
4. ✅ `src/Domain/Model/Product/IProductRepository.php` - Updated type hints (Uuid)
5. ✅ `src/Infrastructure/Persistence/Repository/ProductRepository.php` - Updated type hints

---

## Configuration Details

### Messenger Configuration (config/packages/messenger.yaml)

#### Two Buses
```yaml
buses:
  cqrs_bus:
    # For Commands & Queries (synchronous by default)
    middleware:
      - validation
  
  events_bus:
    # For Domain Events (routed to RabbitMQ)
```

#### Two Transports
```yaml
transports:
  async: '%env(MESSENGER_TRANSPORT_DSN)%'
  rabbitmq: 'amqp://guest:guest@localhost:5672/%2F/events'
```

#### Message Routing
```yaml
routing:
  # CQRS: Commands and Queries
  'App\Application\CQRS\Command\UseCase\CreateProduct': cqrs_bus
  'App\Application\CQRS\Query\UseCase\GetProductList': cqrs_bus
  
  # Events: Routed to RabbitMQ
  'App\Domain\Model\Product\Event\ProductCreated': rabbitmq
```

---

## CQRS Patterns

### Command (Changes State)
```php
// UseCase
class CreateProduct {
    public function __construct(
        ProductTypeEnum $type,
        string $sku,
        string $name,
        string $price,
        int $quantity,
    ) {}
}

// Handler
#[AsMessageHandler]
class CreateProductHandler {
    public function __invoke(CreateProduct $command): Product {
        // Create, validate, save
    }
}
```

### Query (Reads State)
```php
// UseCase
class GetProductList {}

// Handler
#[AsMessageHandler]
class GetProductListHandler {
    public function __invoke(GetProductList $query): array {
        // Fetch and return
    }
}
```

### Domain Event (Notifies)
```php
class ProductCreated {
    public function __construct(
        Uuid $id,
        string $name,
        string $price,
        int $quantity,
    ) {}
}
```

---

## Controller Usage

### Dispatch Commands
```php
$envelope = $this->cqrsBus->dispatch(
    new CreateProduct(
        ProductTypeEnum::PEN,
        'SKU123',
        'Pen Name',
        '2.50',
        100,
    )
);
$handledStamp = $envelope->last(HandledStamp::class);
$product = $handledStamp->getResult();
```

### Dispatch Queries
```php
$envelope = $this->cqrsBus->dispatch(new GetProductList());
$handledStamp = $envelope->last(HandledStamp::class);
$products = $handledStamp->getResult();
```

### Dispatch Events
```php
$this->eventsBus->dispatch(
    new ProductCreated(
        $product->getId(),
        $product->getName(),
        $product->getPrice(),
        $product->getQuantity(),
    )
);
```

---

## Bus Injection in Services

```php
public function __construct(
    // CQRS Bus
    #[\Symfony\Component\DependencyInjection\Attribute\Target('cqrs_bus')]
    private readonly MessageBusInterface $cqrsBus,
    
    // Events Bus
    #[\Symfony\Component\DependencyInjection\Attribute\Target('events_bus')]
    private readonly MessageBusInterface $eventsBus,
) {}
```

---

## API Endpoints

### Create Product
```bash
POST /products
Content-Type: application/json

{
  "name": "Ballpoint Pen",
  "sku": "PEN-001",
  "price": "2.50",
  "quantity": 100
}

Response: 201 Created
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "name": "Ballpoint Pen",
  "price": "2.50",
  "quantity": 100
}
```

### List Products
```bash
GET /products

Response: 200 OK
[
  {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Ballpoint Pen",
    "price": "2.50",
    "quantity": 100
  }
]
```

### Get Single Product
```bash
GET /products/{id}

Response: 200 OK
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "name": "Ballpoint Pen",
  "price": "2.50",
  "quantity": 100
}
```

---

## Commands to Run

### Start RabbitMQ (Docker)
```bash
docker run -d --name rabbitmq \
  -p 5672:5672 \
  -p 15672:15672 \
  rabbitmq:3-management
```

### Process Events (Background Worker)
```bash
php bin/console messenger:consume events -vv
```

### Clear Messenger Queues
```bash
php bin/console messenger:failed:remove
```

### Debug Messages
```bash
php bin/console debug:messenger
```

---

## Factory Pattern (Product Creation)

The system uses a factory to create product subtypes:

```php
// ProductFactory
$product = $factory->create(
    ProductTypeEnum::PEN,    // Creates Pen instance
    'SKU-123',
    'Ballpoint Pen',
    '2.50',
    100,
);

// Also supports PENCIL
$product = $factory->create(
    ProductTypeEnum::PENCIL, // Creates Pencil instance
    'SKU-124',
    'HB Pencil',
    '0.50',
    500,
);
```

---

## Async Event Processing

### How Events Reach RabbitMQ

1. **Dispatch Event**
   ```php
   $eventsBus->dispatch(new ProductCreated(...));
   ```

2. **Router Identifies Transport**
   ```yaml
   'App\Domain\Model\Product\Event\ProductCreated': rabbitmq
   ```

3. **Sends to RabbitMQ**
   ```
   amqp://guest:guest@localhost:5672/%2F/events
   ```

4. **Consumer Processes**
   ```bash
   php bin/console messenger:consume events
   ```

### Multiple Consumers (Scaling)

Run multiple workers in separate terminals/containers:
```bash
# Terminal 1
php bin/console messenger:consume events -vv

# Terminal 2
php bin/console messenger:consume events -vv

# Terminal 3
php bin/console messenger:consume events -vv
```

RabbitMQ will distribute messages across all consumers.

---

## Environment Setup

### .env Configuration

```bash
# CQRS async transport (optional, used by doctrine://default)
MESSENGER_TRANSPORT_DSN=doctrine://default

# RabbitMQ connection (defined in messenger.yaml)
# amqp://guest:guest@localhost:5672/%2F/events
```

### Docker Compose Example

```yaml
version: '3.8'
services:
  app:
    image: php:8.3-cli
    volumes:
      - .:/app
    working_dir: /app
    depends_on:
      - db
      - rabbitmq

  db:
    image: postgres:16
    environment:
      POSTGRES_DB: product_api
      POSTGRES_USER: user
      POSTGRES_PASSWORD: password

  rabbitmq:
    image: rabbitmq:3-management
    ports:
      - "5672:5672"
      - "15672:15672"
    environment:
      RABBITMQ_DEFAULT_USER: guest
      RABBITMQ_DEFAULT_PASS: guest

  event_consumer:
    image: php:8.3-cli
    volumes:
      - .:/app
    working_dir: /app
    command: php bin/console messenger:consume events -vv
    depends_on:
      - rabbitmq
```

---

## Testing

### Unit Test Example

```php
use PHPUnit\Framework\TestCase;
use App\Application\CQRS\Command\UseCase\CreateProduct;
use App\Application\CQRS\Command\Handler\CreateProductHandler;
use App\Domain\Model\Product\ProductTypeEnum;

class CreateProductHandlerTest extends TestCase
{
    public function testCreateProduct(): void
    {
        $command = new CreateProduct(
            ProductTypeEnum::pen,
            'SKU-123',
            'Test Pen',
            '1.50',
            50,
        );
        
        $handler = new CreateProductHandler($repo, $factory);
        $product = $handler($command);
        
        $this->assertEquals('Test Pen', $product->getName());
    }
}
```

---

## Key Features

| Feature | Status | Details |
|---------|--------|---------|
| CQRS Pattern | ✅ | Separate Commands, Queries, Events |
| Two Message Buses | ✅ | cqrs_bus + events_bus |
| Synchronous CQRS | ✅ | Immediate response to client |
| Async Events | ✅ | RabbitMQ processing |
| Type Safety | ✅ | Strong types, Enums, Uuid |
| Validation | ✅ | CQRS middleware validation |
| Factory Pattern | ✅ | Polymorphic product creation |
| Auto-discovery | ✅ | Handlers auto-registered |
| Scalability | ✅ | Multiple event consumers |

---

## Troubleshooting

### RabbitMQ Connection Errors
```bash
# Check if RabbitMQ is running
docker ps | grep rabbitmq

# Check logs
docker logs rabbitmq

# Test connection
rabbitmq-diagnostics ping
```

### Messages Not Processing
```bash
# Check failed messages
php bin/console messenger:failed:show

# Retry failed messages
php bin/console messenger:failed:retry
```

### Handler Not Found
```bash
# List all registered handlers
php bin/console debug:messenger

# Check autoconfiguration
php bin/console debug:autowiring
```

### Validation Failures
```bash
# Enable debug mode to see validation errors
APP_DEBUG=1 php bin/console messenger:consume events -vv
```

---

## Next Steps

1. **Add Event Handlers** - Create listeners for ProductCreated events
2. **Add More Commands** - UpdateProduct, DeleteProduct commands
3. **Add More Queries** - FindProductBySku, SearchProducts queries
4. **Implement Event Sourcing** - Store all events for audit trail
5. **Add Metrics** - Track command/query execution times
6. **Add Logging** - Log all commands, queries, and events

---

## Resources

- [Symfony Messenger Documentation](https://symfony.com/doc/current/messenger.html)
- [CQRS Pattern](https://martinfowler.com/bliki/CQRS.html)
- [Event Sourcing](https://martinfowler.com/eaaDev/EventSourcing.html)
- [RabbitMQ Documentation](https://www.rabbitmq.com/documentation.html)
