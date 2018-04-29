<?php declare(strict_types = 1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/group", name="group_")
 */
final class GroupController extends AbstractController
{
    /**
     * @Route(path="/request-listing", name="request")
     */
    public function requestListing()
    {
        // TODO Handle requests for new group to be listed.
    }

    public function listGroups()
    {
        return $this->render('group/list.html.twig', ['groups' => []]);
    }
}
