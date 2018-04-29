<?php declare(strict_types = 1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class EventController extends AbstractController
{
    public function listEvents(): Response
    {
        return $this->render('event/list.html.twig', ['events' => []]);
    }
}
