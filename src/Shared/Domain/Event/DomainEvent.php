<?php
namespace App\Shared\Domain\Event;

interface DomainEvent
{
    /**
     * Returns the event name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the event description.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Returns the event payload.
     *
     * @return array
     */
    public function getPayload(): array;
}
