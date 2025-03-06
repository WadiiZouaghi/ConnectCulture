<?php

namespace App\Controller;

use App\Entity\Reward;
use App\Form\RewardType;
use App\Repository\RewardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RewardController extends AbstractController
{
    #[Route('/reward/add', name: 'app_reward_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new Reward object
        $reward = new Reward();

        // Create the form using the RewardType
        $form = $this->createForm(RewardType::class, $reward);

        // Handle the form submission
        $form->handleRequest($request);

        // If the form is submitted and valid, save the reward
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reward);
            $entityManager->flush();

            // Redirect to the reward list
            return $this->redirectToRoute('admin_tables');
        }

        // Render the form
        return $this->render('admin/newreward.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
