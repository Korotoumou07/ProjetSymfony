<?php

namespace App\Controller;

use App\Entity\Statut;
use App\Entity\Relance;
use App\Repository\DemandeRepository;
use App\Repository\RelanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RelanceController extends AbstractController
{
    #[Route('/relance', name: 'app_relance')]
    public function index(RelanceRepository $relanceRepository,Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 5;
        $relances = $relanceRepository->findAllRelances();
        $count = $relances->count();
        $nbrePages = ceil($count / $limit);
            
        

        return $this->render('relance/index.html.twig', [
            'relances' => $relances,
            'nbrePages' => $nbrePages,
            'page' => $page,
        ]);
    }

    #[Route('/demande/{id}/relancer', name: 'app_demande_relance')]
    public function relancerDemande(
        int $id,
        DemandeRepository $demandeRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $demande = $demandeRepository->find($id);
    
        if (!$demande) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('app_demandes');
        }
        $demande->setStatut(Statut::ENCOURS);    
        $relance = new Relance();
        $relance->setDateAt(new \DateTimeImmutable());
        $relance->setDescription('Relance envoyée pour une demande annulée.');
        $relance->setDemande($demande);
    
        $entityManager->persist($relance);
        $entityManager->persist($demande);
        $entityManager->flush();
    
        $this->addFlash('success', 'Relance envoyée avec succès.');
        return $this->redirectToRoute('app_client_Demande');
    }



    #[Route('/relance/{id}/details', name: 'app_relance_details')]
    public function details(int $id, RelanceRepository $relanceRepository): Response
    {
        $relance = $relanceRepository->find($id);

        if (!$relance) {
            $this->addFlash('error', 'La relance spécifiée n\'existe pas.');
            return $this->redirectToRoute('app_relances');
        }

        return $this->render('relance/details.html.twig', [
            'relance' => $relance,
        ]);
    }
}
