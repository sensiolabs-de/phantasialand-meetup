<?php declare(strict_types = 1);

namespace App\Controller;

use App\Entity\GroupRequest;
use App\Form\GroupRequestType;
use App\Form\TalkProposalType;
use App\Meetup\Exception\MailerException;
use App\Meetup\Exception\MeetupException;
use App\Meetup\Gateway;
use App\Meetup\Mailer;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use PDOException;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Workflow;

/**
 * @Route("/group", name="group_")
 */
final class GroupController extends AbstractController
{
    /**
     * @Route(path="/request-listing", name="request")
     */
    public function requestListing(Request $request, Mailer $mailer, LoggerInterface $logger): Response
    {
        $form = $this->createForm(GroupRequestType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $groupRequest = $form->getData();
            try {
                $mailer->sendConfirmation($groupRequest);

                $em->persist($groupRequest);
                $em->flush();

                $this->addFlash('success', 'Group request submitted - check your emails.');

                return $this->redirectToRoute('home');
            } catch (MailerException|PDOException|ORMException|DBALException $exception) {
                $logger->error('Group request could not be persisted', ['exception' => $exception]);
                $this->addFlash('danger', 'Error occurred');
            }
        }

        return $this->render('group/request.html.twig', [
            'request_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/confirm/{token}", name="confirm_request")
     */
    public function confirmRequestAction(Workflow $workflow, GroupRequest $groupRequest): Response
    {
        try {
            $workflow->apply($groupRequest, 'confirm');
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Group request was confirmed and will be reviewed. This may take a few days.');
        } catch (LogicException $exception) {
            $this->addFlash('danger', 'Could not confirm group request: '.$exception->getMessage());
        }

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/{urlname}/proposal", name="propose_talk")
     */
    public function proposeTalk(Request $request, Mailer $mailer, LoggerInterface $logger, GroupRequest $groupRequest): Response
    {
        $form = $this->createForm(TalkProposalType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $mailer->sendProposal($form->getData(), $groupRequest);
                $this->addFlash('success', 'Talk proposal submitted');

                return $this->redirectToRoute('group_show', ['urlname' => $groupRequest->getUrlname()]);
            } catch (MailerException $exception) {
                $logger->error('Talk proposal could not be sent', ['exception' => $exception]);
                $this->addFlash('danger', 'Error occurred');
            }
        }

        return $this->render('group/proposal.html.twig', [
            'proposal_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{urlname}", name="show")
     */
    public function showGroup(Gateway $meetupGateway, GroupRequest $groupRequest): Response
    {
        if (!$groupRequest->isApproved()) {
            throw $this->createNotFoundException(sprintf('Group "%s" not approved.', $groupRequest->getUrlname()));
        }
        try {
            $group = $meetupGateway->getGroup($groupRequest->getUrlname());
        } catch (MeetupException $exception) {
            throw new ServiceUnavailableHttpException(60, 'Group could not be loaded', $exception);
        }
        return $this->render('group/show.html.twig', [
            'group' => $group,
        ]);
    }

    public function listGroups(Gateway $meetupGateway): Response
    {
        return $this->render('group/list.html.twig', ['groups' => $meetupGateway->getGroupList()]);
    }
}
