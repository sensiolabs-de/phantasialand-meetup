<?php

declare(strict_types = 1);

namespace App\Meetup;

class Event
{
    private $id;
    private $name;
    private $created;
    private $status;
    private $time;
    private $updated;
    private $utc_offset;
    private $waitlist_count;
    private $yes_rsvp_count;
    private $venue;
    private $group;
    private $link;
    private $description;
    private $visibility;

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreated(): \DateTimeInterface
    {
        return $this->created;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTime(): \DateTimeInterface
    {
        return \DateTimeImmutable::createFromFormat('U', (string)($this->time/1000));
    }

    public function getUpdated(): \DateTimeInterface
    {
        return $this->updated;
    }

    public function getUtcOffset(): \DateInterval
    {
        return $this->utc_offset;
    }

    public function getWaitlistCount(): int
    {
        return $this->waitlist_count;
    }

    public function getYesRsvpCount(): int
    {
        return $this->yes_rsvp_count;
    }

    public function getVenue(): array
    {
        return $this->venue;
    }

    public function getGroup(): array
    {
        return $this->group;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }
}
