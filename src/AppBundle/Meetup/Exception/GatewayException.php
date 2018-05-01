<?php

declare(strict_types = 1);

namespace AppBundle\Meetup\Exception;

class GatewayException extends \DomainException implements MeetupException
{
}
