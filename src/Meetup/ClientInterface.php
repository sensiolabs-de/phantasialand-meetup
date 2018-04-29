<?php

namespace App\Meetup;

use App\Meetup\Exception\MeetupException;

interface ClientInterface
{
    /**
     * @throws MeetupException
     */
    public function getGroup(string $urlname): Group;

    /**
     * @throws MeetupException
     */
    public function getEvent(string $urlname, string $id): Event;

    /**
     * @return Event[]
     * @throws MeetupException
     */
    public function getEventList(string $urlname): array;
}
