# Instructions for Symfony Authentication Platform

## Architecture Overview

**Tech Stack**: Symfony 7.3, Doctrine ORM, Mercure (real-time), Stripe (payments), mPDF (invoices), HWI OAuth

### Project Structure
- `/src/Controller/` - HTTP request handlers with role-based access control
- `/src/Entity/` - Doctrine entities with enum-based status fields
- `/src/Service/` - Domain services (MailerService, StripeService, MessagingService, etc.)
- `/src/Enum/` - Type enums for entity status fields (ConversationType, MessageType, etc.)
- `/src/Security/` - Custom authentication/authorization logic
- `/src/Form/` - Symfony form types for both web and API
- `/assets/styles/` - BEM-based CSS with base/layout/pages structure
- `/templates/components/` - Reusable Twig components (Banner, ServiceBox, etc.)
- `/tests/` - Bootstrap tests only (no full test suite pattern established)

## Entity & Database Patterns

### Status Fields Use Enums
All statuses are stored as `SIMPLE_ARRAY` with enum types, never strings:
```php
#[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: ConversationType::class)]
private array $status = [];

// Usage in controller:
$entity->setStatus([ConversationType::ACTIVE]);  // Wrapped in array
```

### Key Entities
- **Conversation** - Links client User ↔ photographer User with `isFrozen` flag for dispute handling
- **Message** - Belongs to Conversation, sent by User, may reference ServiceProposal
- **ServiceProposal** - Photographer proposes work to client; creates embedded Message
- **ConversationReport** - Audit trail when photographer reports conversation; freezes it
- **User** - Has one-to-one Photographer relationship; multiple roles (ROLE_USER, ROLE_PHOTOGRAPHER)

**Datetime Pattern**: Always use `DateTimeImmutable` with `createdAt` naming convention.

## Controller Patterns

### Security & Access Control
```php
public function someAction(EntityInterface $entity): Response {
    // Deny by role
    $this->denyAccessUnlessGranted('ROLE_PHOTOGRAPHER');
    
    // Verify ownership before operations
    if ($entity->getPhotographer()->getUser()->getId() !== $this->getUser()->getId()) {
        throw $this->createAccessDeniedException();
    }
}
```

### AJAX Endpoints Return JsonResponse
- Conversation reporting, message sending → return `JsonResponse(['status' => 'ok'])`
- Flash messages for redirect actions use `$this->addFlash('success', 'Message')`
- After successful report: `location.reload()` refreshes page to show flash & updated UI

### Rate Limiting
Message sending is rate-limited via `RateLimiterFactoryInterface`:
```php
$this->messageSendingLimiter->create('key')->consume(1);  // Throws RateLimitExceededException
```

### HTML Sanitization
All user input requiring HTML is sanitized:
```php
$sanitized = $htmlSanitizer->sanitizeHtml($userInput);
```

## Frontend Patterns

### Twig Components
Instantiate with kebab-case attribute names:
```twig
<twig:Banner
    title="Chat with {{ participant.firstName }}"
    subtitle="Discuss your project"
    :breadcrumbs="[{label: 'Home', url: path('home'), type: 'link'}]"
/>
```

### CSS Architecture - BEM with Utilities
- **Block**: `.conversation-page { }`
- **Element**: `.conversation__header { }`
- **Modifier**: `.conversation__message--own { }`
- **Utilities**: `.u-display-center`, `.u-img-cover` (single-purpose)
- **CSS Variables**: Use `var(--color-accent)`, `var(--font-heading)` defined in base

### Real-Time with Mercure
JavaScript subscribes to conversation topics on page load:
```js
const eventSource = new EventSource(`${hubUrl}?topic=/conversation/${conversationId}`);
eventSource.onmessage = (e) => {
    const update = JSON.parse(e.data);
    // Update DOM with new message
};
```

### Form Handling via AJAX
Forms use `id`, `data-conversation-id`, `data-user-id` attributes for JS hookups:
```html
<form id="message-form" data-conversation-id="{{ conversation.id }}" data-user-id="{{ app.user.id }}">
```

### Modal Pattern
Modal hidden by default (`display: none`), toggled with `.active` class:
```css
.modal { display: none; }
.modal.active { display: flex; }
```

## Service Layer Conventions

### Domain Services
Each service handles a specific domain (no god-services):
- `MailerService` - Email via Mailgun
- `StripeService` - Payment processing
- `MessagingService` - Message business logic
- `MediaUploader` - File uploads to storage/media

### Dependency Injection
Services injected via constructor dependency injection, never in methods (except for Doctrine entities).

## Debugging & Logging

### Logger Specialization
Monolog instance named `messagesLogger` tracks conversation/message activity:
```php
$this->messagesLogger->warning('Conversation reported', ['conversationId' => $id, ...]);
```

### Common Debug Commands
```bash
docker exec symfony_app php bin/console doctrine:schema:update --force
docker exec symfony_app php bin/console doctrine:migrations:migrate
```

## Common Workflows

### Adding a Report Feature
1. Create `ConversationReport` entity with status enum + foreign keys
2. Add controller method returning `JsonResponse` with redirect signal
3. Add Flash message in controller (not JS alert) for UX
4. Update template conditional to show frozen state based on `isFrozen` property
5. Use `location.reload()` in JS to refresh after successful submission

### Adding a Form
1. Create FormType in `src/Form/`, extend `AbstractType`
2. Render with `{{ form_start/end(form) }}` macro in template
3. Use role-based conditionals: `{% if is_granted('ROLE_PHOTOGRAPHER') %}`
4. Add custom validation callbacks if needed; enums handle status validation

### Adding an Entity Relationship
1. Use `DateTimeImmutable` for timestamps
2. Use enums for status arrays (not strings or integers)
3. Add `orphanRemoval: true` for OneToMany if child should not exist without parent
4. Generate migration: `doctrine:make:migration`
5. Run: `doctrine:schema:update --force` (Docker: wrap with `docker exec symfony_app`)

## Project-Specific Gotchas

1. **Enum Usage**: Always use `ConversationType::ACTIVE` not `'active'` string; wrap in array when setting
2. **User Roles**: A photographed need `ROLE_PHOTOGRAPHER` role + `User.photographer` relationship
3. **Frozen Conversations**: Set `isFrozen = true` + create `ConversationReport` for audit trail
4. **Mercure Topics**: Use `/conversation/{id}` pattern & subscribe with full topic path
5. **Form Name Prefix**: FormType builds HTML with prefixes; use `->getName()` if needed

## File Reference Map

Key files exemplifying patterns:
- [src/Controller/ConversationController.php](src/Controller/ConversationController.php) - AJAX, role checking, rate limiting
- [src/Entity/Conversation.php](src/Entity/Conversation.php) - Enum usage, relationships
- [src/Enum/ConversationType.php](src/Enum/ConversationType.php) - Enum structure
- [templates/chat/show.html.twig](templates/chat/show.html.twig) - Component usage, BEM CSS, forms
- [assets/styles/base/globals.css](assets/styles/base/globals.css) - CSS architecture, button utilities
