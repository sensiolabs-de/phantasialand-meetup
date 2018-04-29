<?php declare(strict_types = 1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class EventController extends AbstractController
{
    public function listEvents()
    {
        return $this->render('event/list.html.twig', ['events' => []]);
    }
}
