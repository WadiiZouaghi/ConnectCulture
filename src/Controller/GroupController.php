<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Actor;
use App\Entity\Invitation;
use App\Form\GroupFormType;
use App\Repository\GroupRepository;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/group/create', name: 'group_create')]
    public function create(Request $request, GroupRepository $groupRepository): Response
    {
        $group = new Group();
        $form = $this->createForm(GroupFormType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($group);
            $this->entityManager->flush();
            $this->addFlash('success', 'Event group created successfully!');
            return $this->redirectToRoute('group_list');
        }

        return $this->render('carint/create_event_group.html.twig', [
            'group_form' => $form->createView(),
        ]);
    }

    #[Route('/groups', name: 'group_list')]
    public function list(GroupRepository $groupRepository): Response
    {
        $groups = $groupRepository->findAll();
        return $this->render('carint/group_list.html.twig', [
            'groups' => $groups,
        ]);
    }

    #[Route('/group/{id}/edit', name: 'group_edit')]
    public function edit(Request $request, Group $group, GroupRepository $groupRepository): Response
    {
        $form = $this->createForm(GroupFormType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($group);
            $this->entityManager->flush();
            $this->addFlash('success', 'Event group updated successfully!');
            return $this->redirectToRoute('group_list');
        }

        return $this->render('carint/edit_event_group.html.twig', [
            'group_form' => $form->createView(),
            'group' => $group,
        ]);
    }

    #[Route('/group/{id}/delete', name: 'group_delete', methods: ['POST'])]
    public function delete(Request $request, Group $group, GroupRepository $groupRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $group->getGroupId(), $request->request->get('_token'))) {
            $this->entityManager->remove($group);
            $this->entityManager->flush();
            $this->addFlash('success', 'Event group deleted successfully!');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/group/{id}/view', name: 'group_view')]
    public function view(Group $group): Response
    {
        return $this->render('carint/group_view.html.twig', [
            'group' => $group,
        ]);
    }

    #[Route('/group/{id}/invite', name: 'group_invite')]
    public function invite(Request $request, Group $group, InvitationRepository $invitationRepository): Response
    {
        // For simplicity, assume any user can invite (remove authentication check)
        // Here, you'd typically have a form to select an invitee (user)
        // For simplicity, let’s assume a hardcoded invitee (e.g., email 'invited@example.com')
        $invitee = $this->entityManager->getRepository(Actor::class)->findOneBy(['email' => 'invited@example.com']);
        if ($invitee && !$group->isMember($invitee)) {
            $invitation = new Invitation();
            $invitation->setGroup($group);
            $invitation->setInviter(null); // No inviter since authentication is not handled
            $invitation->setInvitee($invitee);
            $this->entityManager->persist($invitation);
            $this->entityManager->flush();
            $this->addFlash('success', 'Invitation sent successfully!');
        } else {
            $this->addFlash('error', 'User not found or already a member.');
        }

        return $this->redirectToRoute('group_view', ['id' => $group->getGroupId()]);
    }

    #[Route('/invitation/{id}/accept', name: 'invitation_accept')]
    public function acceptInvitation(Request $request, Invitation $invitation): Response
    {
        // Assume the current user is identified externally (e.g., via session or API)
        // For now, we'll simulate by assuming the invitee is the current user (you'd need to adjust based on external auth)
        $invitee = $invitation->getInvitee();
        if ($invitation->getStatus() === 'pending') {
            $invitation->setStatus('accepted');
            $invitation->getGroup()->addActor($invitee);
            $this->entityManager->flush();
            $this->addFlash('success', 'Invitation accepted! You are now a member of the group.');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/invitation/{id}/decline', name: 'invitation_decline')]
    public function declineInvitation(Request $request, Invitation $invitation): Response
    {
        // Assume the current user is identified externally
        if ($invitation->getStatus() === 'pending') {
            $invitation->setStatus('declined');
            $this->entityManager->flush();
            $this->addFlash('success', 'Invitation declined.');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/group/{id}/invite-request', name: 'group_invite_request')]
    public function inviteRequest(Group $group): Response
    {
        return $this->render('carint/group_invite_request.html.twig', [
            'group' => $group,
        ]);
    }

    #[Route('/group/{id}/invite-request-submit', name: 'group_invite_request_submit')]
    public function submitInviteRequest(Request $request, Group $group, InvitationRepository $invitationRepository): Response
    {
        // Assume the current user is identified externally (e.g., via session or API)
        // For now, we'll simulate by using a placeholder (you'd need to adjust based on external auth)
        $currentUser = $this->entityManager->getRepository(Actor::class)->findOneBy(['email' => 'current@example.com']);
        if (!$currentUser) {
            throw new \LogicException('Current user not found. Authentication must be handled externally.');
        }

        if (!$group->isPublic() && !$group->isMember($currentUser)) {
            $existingInvitation = $invitationRepository->findOneBy([
                'group' => $group,
                'invitee' => $currentUser,
                'status' => 'pending',
            ]);

            if (!$existingInvitation) {
                $invitation = new Invitation();
                $invitation->setGroup($group);
                $invitation->setInvitee($currentUser);
                $invitation->setStatus('pending');
                $this->entityManager->persist($invitation);
                $this->entityManager->flush();
                $this->addFlash('success', 'Your invitation request has been sent to the group admin.');
            } else {
                $this->addFlash('info', 'You already have a pending invitation request for this group.');
            }
        }

        return $this->redirectToRoute('group_list');
    }
}