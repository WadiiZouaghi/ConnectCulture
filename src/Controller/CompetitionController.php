<?php

namespace App\Controller;

use App\Repository\CompetitionRepository;
use App\Entity\Competition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\CompetitionType;
use App\Service\PdfGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use App\Service\EmailService;
use App\Service\TwilioService;
use Symfony\Component\HttpFoundation\JsonResponse;



final class CompetitionController extends AbstractController
{
    private $pdfGenerator;
    private $entityManager;
    private $emailService;
    private $twilioService;


    public function __construct(EntityManagerInterface $entityManager,PdfGenerator $pdfGenerator,EmailService $emailService,TwilioService $twilioService)
    {
        $this->pdfGenerator = $pdfGenerator;
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
        $this->twilioService = $twilioService;

    }
    #[Route('/competition', name: 'app_competition')]
    public function index(): Response
    {
        return $this->render('competition/index.html.twig', [
            'controller_name' => 'CompetitionController',
        ]);
    }
    #[Route('/admin/tables', name: 'admin_tables')]
    public function list(Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager): Response
    {
        $competitionRepository = $entityManager->getRepository(Competition::class);
        $competitionsQuery = $competitionRepository->createQueryBuilder('c')->getQuery();

        $competitions = $paginator->paginate(
            $competitionsQuery,  // The query
            $request->query->getInt('page', 1),  // Current page (default to 1)
            3  // Number of results per page
        );

        return $this->render('/admin/tables.html.twig', [
            'competitions' => $competitions,
        ]);
    }
    #[Route('/admin/competition/remove-user/{id}', name: 'remove_user_from_competition')]
    public function removeUserFromCompetition(int $id, CompetitionRepository $competitionRepository, EntityManagerInterface $entityManager): Response
    {
        // Find the competition by ID
        $competition = $competitionRepository->find($id);
    
        if (!$competition) {
            throw $this->createNotFoundException('Competition not found');
        }
        $competition->setOrganisateur(null);
        $entityManager->persist($competition);
        $entityManager->flush();
        $recipient = '+21694918675';
        $message = 'utilisateur supprimer ';
        $this->twilioService->sendSms($recipient, $message);
        return $this->redirectToRoute('admin_tables');
    }
    #[Route('/admin/competition/delete/{id}', name: 'delete_competition')]
    public function delete(int $id, CompetitionRepository $competitionRepository): Response
    {
        $competition = $competitionRepository->find($id);

        if (!$competition) {
            throw $this->createNotFoundException('Competition not found');
        }

        $this->entityManager->remove($competition);
        $this->entityManager->flush();
        $this->emailService->sendEmail();    
        // Redirect to the admin tables route (ensure it exists)

        return $this->redirectToRoute('admin_tables');
    }
    #[Route('/competition/new', name: 'competition_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $competition = new Competition();
        $form = $this->createForm(CompetitionType::class, $competition);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the competition entity in the database
            $entityManager->persist($competition);
            $entityManager->flush();
            
            // Add a flash message indicating success
            $this->addFlash('success', 'Compétition créée avec succès!');
            return $this->redirectToRoute('admin_tables');
        }
    
        // Render the form in the Twig template
        return $this->render('/admin/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/competition/edit/{id}', name: 'competition_edit')]
    public function edit(Request $request, Competition $competition, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompetitionType::class, $competition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Compétition modifiée avec succès!');
            return $this->redirectToRoute('admin_tables');
        }

        return $this->render('/admin/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/competition/pdf/{id}', name: 'competition_pdf')]
    public function generatePdf(Competition $competition, PdfGenerator $pdfGenerator): Response
    {
        $pdfContent = $pdfGenerator->generatePdf('admin/competition_pdf.html.twig', [
            'competition' => $competition,
        ]);

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="competition_' . $competition->getIdComp() . '.pdf"',
            ]
        );
    }
    #[Route('/admin/competition/qr-code/{id}', name: 'competition_qr_code')]
    public function generateQRCode(int $id): Response
    {
        // Retrieve the competition using the EntityManager
        $competition = $this->entityManager->getRepository(Competition::class)->find($id);

        if (!$competition) {
            throw $this->createNotFoundException('Competition not found');
        }

        // Generate QR Code content
        $competitionDetails = sprintf(
            'Name: %s\nDescription: %s\nStart Date: %s\nEnd Date: %s\nState: %s\nOrganizer ID: %s',
            $competition->getNom(),
            $competition->getDescription(),
            $competition->getDateDebut()->format('M d, Y'),
            $competition->getDateFin()->format('M d, Y'),
            $competition->getEtat() ?: 'Not specified',
            $competition->getOrganisateur() ? $competition->getOrganisateur()->getId() : 'No user assigned'
        );

        // Create the QR Code with specific options
        $qrCode = new QrCode($competitionDetails);

        // Create the PNG writer
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Output the QR code as a PNG image
        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            ['Content-Type' => 'image/png']
        );
    }    
}
