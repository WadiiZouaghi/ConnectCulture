<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Actor;
use App\Entity\Post;
use App\Entity\Invitation;
use App\Form\GroupFormType;
use App\Repository\GroupRepository;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
            $coverPicture = $form->get('coverPicture')->getData();
            if ($coverPicture) {
                try {
                    $binaryContent = file_get_contents($coverPicture->getPathname());
                    $group->setCoverPicture($binaryContent);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to process cover picture: ' . $e->getMessage());
                    return $this->render('carint/create_event_group.html.twig', [
                        'group_form' => $form->createView(),
                    ]);
                }
            }

            // Proceed without a user (anonymous creation)
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
    public function list(Request $request, GroupRepository $groupRepository): Response
    {
        $searchQuery = $request->query->get('q', '');
        $location = $request->query->get('location', '');
        $visibility = $request->query->get('visibility', '');
        $date = $request->query->get('date', '');

        $groups = $groupRepository->findBySearchQueryWithFilters($searchQuery, $location, $visibility, $date);
        $locations = $groupRepository->findUniqueLocations();
        $visibilities = $groupRepository->findUniqueVisibilities();

        return $this->render('carint/group_list.html.twig', [
            'groups' => $groups,
            'searchQuery' => $searchQuery,
            'location' => $location,
            'visibility' => $visibility,
            'date' => $date,
            'locations' => $locations,
            'visibilities' => $visibilities,
        ]);
    }

    #[Route('/group/{id}/edit', name: 'group_edit')]
    public function edit(Request $request, Group $group, GroupRepository $groupRepository): Response
    {
        $form = $this->createForm(GroupFormType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverPicture = $form->get('coverPicture')->getData();
            if ($coverPicture) {
                try {
                    $binaryContent = file_get_contents($coverPicture->getPathname());
                    $group->setCoverPicture($binaryContent);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to process cover picture: ' . $e->getMessage());
                    return $this->render('carint/edit_event_group.html.twig', [
                        'group_form' => $form->createView(),
                        'group' => $group,
                    ]);
                }
            }

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
        if ($this->isCsrfTokenValid('delete' . $group->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($group);
            $this->entityManager->flush();
            $this->addFlash('success', 'Event group deleted successfully!');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/group/{id}/view', name: 'group_view')]
    public function view(Group $group): Response
    {
        $base64Image = null;
        if ($group->getCoverPicture()) {
            $binaryContent = stream_get_contents($group->getCoverPicture());
            $base64Image = base64_encode($binaryContent);
        }

        return $this->render('carint/group_view.html.twig', [
            'group' => $group,
            'base64Image' => $base64Image,
        ]);
    }

    #[Route('/group/{id}/posts', name: 'group_posts')]
    public function posts(Group $group): Response
    {
        $posts = $this->entityManager->getRepository(Post::class)->findBy(['group' => $group], ['createdAt' => 'DESC']);
        return $this->render('carint/group_posts.html.twig', [
            'group' => $group,
            'posts' => $posts,
        ]);
    }

    #[Route('/group/{id}/invite', name: 'group_invite')]
    public function invite(Request $request, Group $group, InvitationRepository $invitationRepository): Response
    {
        // Proceed anonymously without requiring an authenticated user
        $currentUser = null; // No inviter (anonymous invitation)

        // Find or create an invitee (replace with actual logic or form for email input)
        $inviteeEmail = 'invited@example.com'; // Replace with dynamic input or form data
        $invitee = $this->entityManager->getRepository(Actor::class)->findOneBy(['email' => $inviteeEmail]);

        if ($invitee && !$group->isMember($invitee)) {
            $invitation = new Invitation();
            $invitation->setGroup($group);
            if ($currentUser) {
                $invitation->setInviter($currentUser); // Set inviter only if a user exists (not in this case)
            }
            $invitation->setInvitee($invitee);
            $this->entityManager->persist($invitation);
            $this->entityManager->flush();
            $this->addFlash('success', 'Invitation sent successfully!');
        } else {
            $this->addFlash('error', 'User not found or already a member.');
        }
        return $this->redirectToRoute('group_view', ['id' => $group->getId()]);
    }

    #[Route('/invitation/{id}/accept', name: 'invitation_accept')]
    public function acceptInvitation(Request $request, Invitation $invitation): Response
    {
        // Proceed anonymously or skip if no user is needed
        // For now, allow anonymous acceptance (remove security check)
        // If you want to enforce user authentication later, reintroduce getUser()
        if ($invitation->getStatus() === 'pending') {
            $invitation->setStatus('accepted');
            // Optionally, add a default or no user to the group
            // $invitation->getGroup()->addActor(/* some default Actor or skip */);
            $this->entityManager->flush();
            $this->addFlash('success', 'Invitation accepted! You are now a member of the group (anonymous).');
        }
        return $this->redirectToRoute('group_list');
    }

    #[Route('/invitation/{id}/decline', name: 'invitation_decline')]
    public function declineInvitation(Request $request, Invitation $invitation): Response
    {
        // Proceed anonymously or skip if no user is needed
        // For now, allow anonymous decline (remove security check)
        // If you want to enforce user authentication later, reintroduce getUser()
        if ($invitation->getStatus() === 'pending') {
            $invitation->setStatus('declined');
            $this->entityManager->flush();
            $this->addFlash('success', 'Invitation declined (anonymous).');
        }
        return $this->redirectToRoute('group_list');
    }

    #[Route('/group/{id}/invite-request', name: 'group_invite_request')]
    public function inviteRequest(Group $group): Response
    {
        return $this->render('carint/group_invite_request.html.twig', ['group' => $group]);
    }

    #[Route('/group/{id}/invite-request-submit', name: 'group_invite_request_submit')]
    public function submitInviteRequest(Request $request, Group $group, InvitationRepository $invitationRepository): Response
    {
        // Proceed anonymously or skip if no user is needed
        // For now, allow anonymous request (remove security check)
        // If you want to enforce user authentication later, reintroduce getUser()
        $currentUser = null; // No inviter (anonymous request)

        if (!$group->isPublic() && (!$currentUser || !$group->isMember($currentUser))) {
            $existingInvitation = $invitationRepository->findOneBy(['group' => $group, 'invitee' => $currentUser, 'status' => 'pending']);
            if (!$existingInvitation) {
                $invitation = new Invitation();
                $invitation->setGroup($group)->setInvitee($currentUser)->setStatus('pending');
                $this->entityManager->persist($invitation);
                $this->entityManager->flush();
                $this->addFlash('success', 'Your invitation request has been sent to the group admin (anonymous).');
            } else {
                $this->addFlash('info', 'You already have a pending invitation request for this group.');
            }
        }
        return $this->redirectToRoute('group_list');
    }
}