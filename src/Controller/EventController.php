<?php declare(strict_types = 1);

namespace App\Controller;

use App\Entity\GroupRequest;
use App\Meetup\Exception\GatewayException;
use App\Meetup\Gateway;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use function var_dump;

/**
 * @Route("/event", name="event_")
 */
final class EventController extends AbstractController
{
    private $meetupGateway;

    public function __construct(Gateway $meetupGateway)
    {
        $this->meetupGateway = $meetupGateway;
    }

    /**
     * @Route("/{urlname}/{eventId}", name="show")
     */
    public function showEvent(GroupRequest $groupRequest, string $eventId): Response
    {
        if (!$groupRequest->isApproved()) {
            throw $this->createNotFoundException(sprintf('Group "%s" not approved.', $groupRequest->getUrlname()));
        }
        try {
            $event = $this->meetupGateway->getEvent($groupRequest->getUrlname(), $eventId);
        } catch (GatewayException $exception) {
            throw new ServiceUnavailableHttpException(60, 'Event could not be loaded', $exception);
        }
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    public function listEvents(?string $urlname): Response
    {
        return $this->render('event/list.html.twig', ['events' => $this->meetupGateway->getEventList($urlname)]);
    }
}
