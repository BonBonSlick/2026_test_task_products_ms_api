# Message Bus Architecture Diagram

## System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                        ProductController                             │
│  (Handles HTTP Requests)                                             │
└────────────────────────────┬────────────────────────────────────────┘
                             │
                    ┌────────┴────────┐
                    ▼                 ▼
          ┌──────────────────┐  ┌──────────────────┐
          │   CQRS Bus       │  │   Events Bus     │
          │  cqrs_bus        │  │  events_bus      │
          └────┬─────────────┘  └────┬─────────────┘
               │                     │
       ┌───────┴────────┐            │
       ▼                ▼            ▼
  ┌─────────┐   ┌─────────┐   ┌──────────────┐
  │Commands │   │ Queries │   │Domain Events │
  │         │   │         │   │              │
  │CREATE   │   │ GET     │   │PRODUCT       │
  │PRODUCT  │   │ LIST    │   │CREATED       │
  └────┬────┘   └────┬────┘   └──────┬───────┘
       │             │               │
       └─────┬───────┘               │
             │                       │
             ▼                       ▼
      ┌──────────────┐        ┌─────────────────┐
      │  Handlers    │        │ RabbitMQ        │
      │  (Sync)      │        │ Transport       │
      └──────┬───────┘        └────────┬────────┘
             │                         │
             ▼                         ▼
      ┌──────────────┐        ┌─────────────────┐
      │  Repository  │        │ Message Queue   │
      │  (Database)  │        │ (Async)         │
      └──────────────┘        └─────────────────┘
```

## Message Bus Configuration

### 1. CQRS Bus (`cqrs_bus`)
```
Purpose: Handle Commands and Queries synchronously
Middleware: allow_no_handlers, validation

Routes:
  ├─ CreateProduct Command
  │  └─> CreateProductHandler (synchronous)
  │      └─> Factory creates Product
  │      └─> Repository saves to Database
  │
  └─ GetProductList Query
     └─> GetProductListHandler (synchronous)
         └─> Repository fetches from Database
```

### 2. Events Bus (`events_bus`)
```
Purpose: Dispatch Domain Events to RabbitMQ asynchronously
Transport: RabbitMQ (amqp://...)

Routes:
  └─ ProductCreated Event
     └─> RabbitMQ Transport
         └─> Message Queue (async processing)
```

## Request Flow Examples

### Scenario 1: Create Product
```
1. Client sends POST /products
   └─> ProductController::create()
       
2. Dispatch CreateProduct command via cqrs_bus
   └─> CreateProductHandler receives it
       ├─> ProductFactory->create() [creates Pen or Pencil instance]
       ├─> ProductRepository->save() [persists to DB]
       └─> Returns Product entity
       
3. Dispatch ProductCreated event via events_bus
   └─> RabbitMQ Transport
       └─> Adds to events queue
       └─> Async workers consume later
       
4. Return JSON response (201 Created)
```

### Scenario 2: List Products
```
1. Client sends GET /products
   └─> ProductController::list()
   
2. Dispatch GetProductList query via cqrs_bus
   └─> GetProductListHandler receives it
       └─> ProductRepository->findAllProducts() [queries DB]
       └─> Returns Product[] array
       
3. Return JSON response (200 OK)
```

### Scenario 3: Get Single Product
```
1. Client sends GET /products/{id}
   └─> ProductController::show()
   
2. Direct repository access (no event needed)
   └─> ProductRepository->findById(Uuid) [queries DB]
   
3. Return JSON response (200 OK) or 404
```

## Message Bus Middleware

### CQRS Bus Middleware Stack
```
Request
   ↓
[allow_no_handlers]  ← Allows commands with no handlers
   ↓
[validation]         ← Validates command data structure
   ↓
Command/Query Handler
   ↓
Response
```

### Events Bus Middleware Stack
```
Request
   ↓
[allow_no_handlers]  ← Allows events to be dispatched without handlers
   ↓
Event Router
   ↓
RabbitMQ Transport
```

## Service Injection in ProductController

```php
public function __construct(
    // Repository for direct access (used in show action)
    private readonly IProductRepository $productRepository,
    
    // CQRS Bus - targets the 'cqrs_bus' service
    #[Target('cqrs_bus')]
    private readonly MessageBusInterface $cqrsBus,
    
    // Events Bus - targets the 'events_bus' service
    #[Target('events_bus')]
    private readonly MessageBusInterface $eventsBus,
) {}
```

## Handler Auto-Discovery

All handlers are auto-discovered via `#[AsMessageHandler]` attribute:

```php
#[AsMessageHandler]
final class CreateProductHandler {
    public function __invoke(CreateProduct $command): Product { ... }
}
```

The Symfony Messenger automatically:
1. Discovers the handler
2. Maps it to the correct bus based on message type routing
3. Registers it as a service
4. Injects it when dispatching messages

## Event Processing (RabbitMQ)

### Manual Event Processing (Development)
```bash
php bin/console messenger:consume events -vv
```

### Background Worker (Production)
```bash
php bin/console messenger:consume events --time-limit=3600 -vv
```

### Docker Compose Example
```yaml
services:
  rabbitmq:
    image: rabbitmq:3-management
    ports:
      - "5672:5672"    # AMQP port
      - "15672:15672"  # Management UI
    environment:
      RABBITMQ_DEFAULT_USER: guest
      RABBITMQ_DEFAULT_PASS: guest
  
  event_worker:
    build: .
    command: php bin/console messenger:consume events -vv
    depends_on:
      - rabbitmq
```

## Environment Variables

```bash
# .env or .env.local

# For async transport (optional)
MESSENGER_TRANSPORT_DSN=doctrine://default

# RabbitMQ is configured in messenger.yaml:
# rabbitmq: 'amqp://guest:guest@localhost:5672/%2F/events'
```

## Benefits of This Architecture

✅ **Separation of Concerns**
   - Commands change state (create product)
   - Queries read state (get products)
   - Events notify interested parties

✅ **Synchronous CQRS Processing**
   - Commands/Queries execute immediately
   - Client gets immediate response
   - Consistent state guaranteed

✅ **Asynchronous Event Processing**
   - Events processed by background workers
   - Decoupled from main request
   - Can be consumed by multiple services

✅ **Scalability**
   - Add more event workers without scaling main app
   - Multiple consumers can process events
   - Load balancing via RabbitMQ

✅ **Extensibility**
   - Easy to add new commands/queries
   - Easy to add new event handlers
   - No coupling between components

✅ **Testing**
   - Commands can be tested in isolation
   - Queries can be tested independently
   - Events can be stubbed/mocked

## Type Safety & Validation

The system uses strong typing:
- ProductTypeEnum (PEN, PENCIL)
- Uuid for all IDs
- Type hints on all messages and handlers
- Validation middleware for CQRS messages

## Summary

This architecture provides:
1. **Clear separation** between Commands (create), Queries (read), and Events (notify)
2. **Synchronous processing** for CQRS to ensure consistency
3. **Asynchronous processing** for events via RabbitMQ for scalability
4. **Type safety** with strict types and enums
5. **Extensibility** for adding new features without breaking existing code
