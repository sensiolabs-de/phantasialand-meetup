<?php

declare(strict_types = 1);

namespace AppBundle\Controller;

use AppBundle\Entity\GroupRequest;
use AppBundle\Form\GroupRequestType;
use AppBundle\Form\TalkProposalType;
use AppBundle\Meetup\Exception\MailerException;
use AppBundle\Meetup\Exception\MeetupException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Exception\LogicException;

class GroupController extends Controller
{
    /**
     * @Route("/group-request/", name="group_request")
     */
    public function requestAction(Request $request): Response
    {
        $form = $this->createForm(GroupRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $groupRequest = $form->getData();
            try {
                $this->get('app.mailer')->sendConfirmation($groupRequest);
                $em->persist($groupRequest);
                $em->flush();

                $this->addFlash('success', 'Group request submitted - check your emails.');

                return $this->redirectToRoute('home');
            } catch (MailerException|PDOException|ORMException|DBALException $exception) {
                $this->get('logger')->error('Group request could not be persisted', ['exception' => $exception]);
                $this->addFlash('danger', 'Error occurred');
            }
        }

        return $this->render('group/request.html.twig', [
            'request_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/group-request/{token}", name="group_request_confirmation")
     */
    public function confirmRequestAction(GroupRequest $groupRequest): RedirectResponse
    {
        try {
            $this->get('workflow.group_request')->apply($groupRequest, 'confirm');

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Group request was confirmed and will be reviewed. This may take a few days.');
        } catch (LogicException $exception) {
            $this->addFlash('danger', 'Could not confirm group request: '.$exception->getMessage());
        }

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/{urlname}/talk-proposal", name="group_proposal")
     */
    public function proposalAction(
        Request $request,
        GroupRequest $groupRequest
    ): Response {
        $form = $this->createForm(TalkProposalType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->get('app.mailer')->sendProposal($form->getData(), $groupRequest);
                $this->addFlash('success', 'Talk proposal submitted');

                return $this->redirectToRoute('group', ['urlname' => $groupRequest->getUrlname()]);
            } catch (MailerException $exception) {
                $this->get('logger')->error('Talk proposal could not be sent', ['exception' => $exception]);
                $this->addFlash('danger', 'Error occurred');
            }
        }

        return $this->render('group/proposal.html.twig', [
            'proposal_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{urlname}", name="group")
     */
    public function showAction(GroupRequest $groupRequest): Response
    {
        if (!$groupRequest->isApproved()) {
            throw $this->createNotFoundException(sprintf('Group "%s" not approved.', $groupRequest->getUrlname()));
        }

        try {
            $group = $this->get('app.gateway')->getGroup($groupRequest->getUrlname());
        } catch (MeetupException $exception) {
            throw new ServiceUnavailableHttpException(60, 'Group could not be loaded', $exception);
        }

        return $this->render('group/show.html.twig', [
            'group' => $group,
        ]);
    }

    public function listAction(): Response
    {
        return $this->render('group/list.html.twig', [
            'groups' => $this->container->get('app.gateway')->getGroupList()
        ]);
    }
}
