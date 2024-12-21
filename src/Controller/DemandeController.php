<?php

namespace App\Controller;

use App\Entity\Statut;
use App\Entity\Demande;
use App\Entity\Relance;
use App\Form\DemandeType;
use App\Entity\Demande_Article;
use App\Repository\ArticleRepository;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DemandeController extends AbstractController
{
 


#[Route('/demande', name: 'app_demande')]
public function manageDemandes(
    DemandeRepository $demandeRepository,
    ArticleRepository $articleRepository,
    EntityManagerInterface $entityManager,
    Request $request
): Response {
    $page = $request->query->getInt('page', 1);
    $limit = 5;
    $page1 = $request->query->getInt('page1', 1);
    $limit1 = 2;

    $demandes = $demandeRepository->findAllDemandes($page, $limit);
    $articles = $articleRepository->findByStatut("DIS", $page1, $limit1);

    $nbrePages = (int) ceil($demandes->count() / $limit);
    $nbrePages1 = (int) ceil($articles->count() / $limit1);

    $demande = new Demande();
    $form = $this->createForm(DemandeType::class, $demande);
    $form->handleRequest($request);


    $errors = []; 

    if ($form->isSubmitted()) {
        if (!$form->isValid()) {
            foreach ($form->getErrors(true, true) as $error) {
                $errors[] = $error->getMessage();
            }

            return $this->render('demande/index.html.twig', [
                'articles' => $articles,
                'demandes' => $demandes,
                'nbrePages' => $nbrePages,
                'page' => $page,
                'nbrePages1' => $nbrePages1,
                'page1' => $page1,
                'demandeForm' => $form->createView(),
                'errors' => $errors,
                'showModal' => true,
            ]);
        }

        $articlesData = $request->request->get('selected_articles');
        $selectedArticles = json_decode($articlesData, true);

        if (empty($selectedArticles)) {
            $errors[] = 'Aucun article sélectionné.';
        } else {
            $totalMontant = 0;
            foreach ($selectedArticles as $articleData) {
                $article = $articleRepository->find($articleData['id']);
                $quantity = (int) $articleData['quantite'];

                if (!$article || $quantity <= 0) {
                    $errors[] = "L'article sélectionné est invalide.";
                    continue;
                }

                if ($article->getQteStock() < $quantity) {
                    $errors[] = "La quantité demandée pour l'article {$article->getNomArticle()} dépasse le stock disponible.";
                } else {
                    $article->setQteStock($article->getQteStock() - $quantity);

                    $demandeArticle = new Demande_Article();
                    $demandeArticle->setArticle($article);
                    $demandeArticle->setQte($quantity);
                    $demandeArticle->setDemande($demande);

                    $entityManager->persist($demandeArticle);

                    $totalMontant += $quantity * $article->getPrix();
                }
            }

            if (empty($errors)) {
                $demande->setDateAt(new \DateTimeImmutable());
                $demande->setMontant($totalMontant);
                $demande->setStatut(Statut::ENCOURS);

                $entityManager->persist($demande);
                $entityManager->flush();

                $this->addFlash('success', 'Demande créée avec succès.');
                return $this->redirectToRoute('app_demande');
            }
        }

        if (!empty($errors)) {
            return $this->render('demande/index.html.twig', [
                'articles' => $articles,
                'demandes' => $demandes,
                'nbrePages' => $nbrePages,
                'page' => $page,
                'nbrePages1' => $nbrePages1,
                'page1' => $page1,
                'demandeForm' => $form->createView(),
                'errors' => $errors,
                'showModal' => true,
            ]);
        }
    }

    return $this->render('demande/index.html.twig', [
        'articles' => $articles,
        'demandes' => $demandes,
        'nbrePages' => $nbrePages,
        'page' => $page,
        'nbrePages1' => $nbrePages1,
        'page1' => $page1,
        'demandeForm' => $form->createView(),
        'errors' => $errors,
        
    ]);
}



#[Route('/demande/{statut}', name: 'app_demande_enCours')]
public function DemandeEnCours(
    string $statut,
    DemandeRepository $demandeRepository,
    ArticleRepository $articleRepository,
    EntityManagerInterface $entityManager,
    Request $request
): Response {
    $page = $request->query->getInt('page', 1);
    $limit = 5;
    $page1 = $request->query->getInt('page1', 1);
    $limit1 = 2;

    $demandes = $demandeRepository->findDemandesByEncours($statut, $page, $limit);
    $articles = $articleRepository->findByStatut("DIS", $page1, $limit1);

    $count = $demandes->count();
    $nbrePages = ceil($count / $limit);
    $nbrePages1 = (int) ceil($articles->count() / $limit1);

    $demande = new Demande();
    $form = $this->createForm(DemandeType::class, $demande);
    $form->handleRequest($request);

    $errors = []; 
    $showModal = false; 

    if ($form->isSubmitted()) {
        $showModal = true;

        if (!$form->isValid()) {
            foreach ($form->getErrors(true, true) as $error) {
                $errors[] = $error->getMessage();
            }
        } else {
            $articlesData = $request->request->get('selected_articles');
            $selectedArticles = json_decode($articlesData, true);

            if (empty($selectedArticles)) {
                $errors[] = 'Aucun article sélectionné.';
            } else {
                $totalMontant = 0;

                foreach ($selectedArticles as $articleData) {
                    $article = $articleRepository->find($articleData['id']);
                    $quantity = (int) $articleData['quantite'];

                    if (!$article || $quantity <= 0) {
                        $errors[] = "L'article sélectionné est invalide.";
                        continue;
                    }

                    if ($article->getQteStock() < $quantity) {
                        $errors[] = "La quantité demandée pour l'article {$article->getNomArticle()} dépasse le stock disponible.";
                    } else {
                        $article->setQteStock($article->getQteStock() - $quantity);

                        $demandeArticle = new Demande_Article();
                        $demandeArticle->setArticle($article);
                        $demandeArticle->setQte($quantity);
                        $demandeArticle->setDemande($demande);

                        $entityManager->persist($demandeArticle);

                        $totalMontant += $quantity * $article->getPrix();
                    }
                }

                if (empty($errors)) {
                    $demande->setDateAt(new \DateTimeImmutable());
                    $demande->setMontant($totalMontant);
                    $demande->setStatut(Statut::ENCOURS);

                    $entityManager->persist($demande);
                    $entityManager->flush();

                    $this->addFlash('success', 'Demande créée avec succès.');
                    return $this->redirectToRoute('app_demande_enCours', ['statut' => $statut]);
                }
            }
        }
    }

    return $this->render('demande/index.html.twig', [
        'demandes' => $demandes,
        'articles' => $articles,
        'nbrePages' => $nbrePages,
        'page' => $page,
        'nbrePages1' => $nbrePages1,
        'page1' => $page1,
        'demandeForm' => $form->createView(),
        'errors' => $errors,
        'showModal' => $showModal,
    ]);
}




    



    #[Route('/demande/{id}/details', name: 'app_detail_demande')]
    public function detailDemande(int $id, DemandeRepository $demandeRepository,Request $request): Response
    {

        $demande = $demandeRepository->find($id);
       
    
        if (!$demande) {
            throw $this->createNotFoundException('Demande non trouvée');
        }
        $page = $request->query->getInt('page',1);
        $limit=5;
    
        $details = $demande->getDemandeArticle();
       
        $count=$details->count();

        $nbrePages=ceil($count/$limit);

        $total = 0;
        foreach ($details as $detail) {
            $total += $detail->getArticle()->getPrix() * $detail->getQte();
        }
    
        return $this->render('demande/details.html.twig', [
            'demande' => $demande,
            'details' => $details,
            'total' => $total,
            'nbrePages'=>$nbrePages,
            'page'=>$page,
        ]);
    }
    

    #[Route('/demande/valider/{id}', name: 'app_valider_demande')]
    public function validerDemande(int $id, DemandeRepository $demandeRepository, EntityManagerInterface $entityManager): Response
    {
        $demande = $demandeRepository->find($id);
    
        if (!$demande) {
            throw $this->createNotFoundException('La demande n\'existe pas.');
        }
    
        $demande->setStatut(Statut::ACCEPTE);

        $entityManager->persist($demande);
        $entityManager->flush();
    
        $this->addFlash('success', 'La demande a été validée.');
    
        return $this->redirectToRoute('app_detail_demande', ['id' => $id]);
    }
    
    #[Route('/demande/refuser/{id}', name: 'app_refuser_demande')]
    public function refuserDemande(int $id, DemandeRepository $demandeRepository, EntityManagerInterface $entityManager): Response
    {
        $demande = $demandeRepository->find($id);
    
        if (!$demande) {
            throw $this->createNotFoundException('La demande n\'existe pas.');
        }
    
        $demande->setStatut(Statut::ANNULE);

        $entityManager->persist($demande);
        $entityManager->flush();
    
        $this->addFlash('error', 'La demande a été refusée.');
    
        return $this->redirectToRoute('app_detail_demande', ['id' => $id]);
    }
   
    


 
}
