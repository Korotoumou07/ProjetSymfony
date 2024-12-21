<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Form\PaymentType;
use App\Repository\DetteRepository;
use App\Repository\ArticleRepository;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaymentController extends AbstractController
{
    #[Route('/payment/{idDette}', name: 'app_payment')]
    public function index(
        int $idDette,
        Request $request,
        PaymentRepository $paymentRepository,
        DetteRepository $detteRepository,
        ArticleRepository $articleRepository,
        EntityManagerInterface $entityManagerInterface
    ): Response {
        $dette = $detteRepository->find($idDette);
    
        if (!$dette) {
            throw $this->createNotFoundException('Dette introuvable.');
        }
    
        $pageArticles = $request->query->getInt('pageArticles', 1);
        $pagePaiements = $request->query->getInt('pagePaiements', 1);
        $limit = 2;
    
        $paiements = $paymentRepository->findByDette($idDette, $pagePaiements, $limit);
        $countPaiements = count($paiements);
        $nbrePagesPaiements = ceil($countPaiements / $limit);
    
        $articles = $articleRepository->findByDette($idDette, $pageArticles, $limit);
       
        $countDetails = $articles->count();
        $nbrePagesArticles = ceil($countDetails / $limit);
    
        
    
        $paiement = new Payment();
        $form = $this->createForm(PaymentType::class, $paiement);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $montantRestant = $dette->getMontant() - $dette->getMontantVerser();
            if ($paiement->getMontant() > $montantRestant) {
                $this->addFlash('error', 'Le montant du paiement doit être inférieur ou égal au montant restant !');
            } else {
                $dette->setMontantVerser($dette->getMontantVerser() + $paiement->getMontant());
                $dette->setMontantRestant($dette->getMontant() - $dette->getMontantVerser());
                $paiement->setDette($dette);
    
                $entityManagerInterface->persist($paiement);
                $entityManagerInterface->flush();
    
                return $this->redirectToRoute('app_payment', ['idDette' => $idDette]);
            }
        }
    
        $activeTab = $request->query->get('tab', 'articles');
    
        return $this->render('payment/index.html.twig', [
            'paiements' => $paiements,
            'articles' => $articles,
            'nbrePagesArticles' => $nbrePagesArticles,
            'nbrePagesPaiements' => $nbrePagesPaiements,
            'pageArticles' => $pageArticles,
            'pagePaiements' => $pagePaiements,
            'dette' => $dette,
            'form' => $form->createView(),
            'disabled' => $dette->getMontant() == $dette->getMontantVerser(),
            'activeTab' => $activeTab,
        ]);
    }
    
    

 
   
}





