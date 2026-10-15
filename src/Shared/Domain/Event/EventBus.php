<?php
namespace App\Shared\Domain\Event;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventBus
{
    /** @var DomainEvent[] */
    private array $events = [];

    /**
     * Creates the domain event bus.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    /**
     * Record a new domain event.
     *
     * @param DomainEvent $event
     */
    public function recordEvent(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    /**
     * Dispatch all domain events.
     */
    public function dispatchEvents(): void
    {
        $events       = $this->events;
        $this->events = [];

        foreach ($events as $event) {
            $this->dispatcher->dispatch($event, $event->getName());
        }
    }
}
