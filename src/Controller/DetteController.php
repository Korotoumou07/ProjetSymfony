<?php

namespace App\Controller;

use App\Entity\Dette;
use DateTimeImmutable;
use App\Entity\Payment;
use App\Entity\Relance;
use App\Form\DetteType;
use App\Entity\Dette_Article;
use App\Repository\DetteRepository;
use App\Repository\ClientRepository;
use App\Repository\ArticleRepository;
use App\Repository\DemandeRepository;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DetteController extends AbstractController
{





#[Route('/dette', name: 'app_dette')]
public function allDettes(
    DetteRepository $detteRepository,
    EntityManagerInterface $entityManager,
    ArticleRepository $articleRepository,
    ValidatorInterface $validator,
    Request $request
): Response {
    // Pagination configuration
    $page = $request->query->getInt('page', 1);
    $limit = 5;
    $page1 = $request->query->getInt('page1', 1);
    $limit1 = 2;

    // Fetch data for dettes and articles
    $dettes = $detteRepository->findAllNonArchivedDettes($page, $limit);
    $articles = $articleRepository->findByStatut("ENCOURS", $page1, $limit1);

    // Calculate pagination counts
    $nbrePages = (int) ceil($dettes->count() / $limit);
    $nbrePages1 = (int) ceil($articles->count() / $limit1);

    // Calculate totals for dettes
    // Calcul des totaux
    $Alldettes = $detteRepository->findNonArchivedDettes();
    $montantTotal = $montantVerserTotal = $montantRestantTotal = 0;
    foreach ($Alldettes as $dette) {
        $montantTotal += $dette->getMontant();
        $montantVerserTotal += $dette->getMontantVerser();
        $montantRestantTotal += $dette->getMontantRestant();
    }
    // Create and handle form
    $dette = new Dette();
    $form = $this->createForm(DetteType::class, $dette);
    $form->handleRequest($request);

    $errors = []; // Error container for displaying issues

    if ($form->isSubmitted()) {
        // Collect form validation errors
        if (!$form->isValid()) {
            foreach ($form->getErrors(true, true) as $error) {
                $errors[] = $error->getMessage();
            }
           

            return $this->render('dette/index.html.twig', [
                'articles' => $articles,
                'dettes' => $dettes,
                'nbrePages' => $nbrePages,
                'page' => $page,
                'nbrePages1' => $nbrePages1,
                'page1' => $page1,
                'montantTotal' => $montantTotal,
                'montantVerserTotal' => $montantVerserTotal,
                'montantRestantTotal' => $montantRestantTotal,
                'form' => $form->createView(),
                'errors' => $errors,
                'showModal' => true,
            ]);

        }

        // Process custom validation for articles
        $articlesData = $request->request->get('selected_articles');
        $selectedArticles = json_decode($articlesData, true);

        if (empty($selectedArticles)) {
            $errors[] = 'Aucun article sélectionné.';
        } else {
            $totalMontant = 0;

            foreach ($selectedArticles as $articleData) {
                // dd($articleData);
                $article = $articleRepository->find($articleData['id']);
                $quantity = (int) $articleData['quantity'];

                if (!$article || $quantity <= 0) {
                    $errors[] = "L'article sélectionné est invalide.";
                    continue;
                }

                if ($article->getQteStock() < $quantity) {
                    $errors[] = "La quantité demandée pour l'article {$article->getNomArticle()} dépasse le stock disponible.";
                } else {
                    $article->setQteStock($article->getQteStock() - $quantity);
                    $detteArticle = new Dette_Article();
                    $detteArticle->setArticle($article);
                    $detteArticle->setQte($quantity);
                    $detteArticle->setDette($dette);
                        
                    $entityManager->persist($detteArticle);
                    $totalMontant += $quantity * $article->getPrix();
                }
            }

            // Handle form data if there are no article-related errors
            if (empty($errors)) {
                $montantVerser = $form->get('montantVerser')->getData();
                $montantRestant = $totalMontant - $montantVerser;
                

                if ($montantVerser > $totalMontant) {
                    $errors[] = 'Le montant versé ne peut pas dépasser le montant total.';
                    
                } else {
                    $dette->setMontant($totalMontant);
                    $dette->setMontantRestant($montantRestant);
                    $dette->setDateAt(new \DateTimeImmutable());


                     // Créer un paiement pour le montant versé
                    $payment = new Payment();
                    $payment->setMontant($montantVerser);
                    $payment->setDette($dette);
                    $payment->setDateAt(new \DateTimeImmutable());
                    $entityManager->persist($payment);

                    $entityManager->persist($dette);
                    $entityManager->flush();

                    $this->addFlash('success', 'Dette créée avec succès.');
                    return $this->redirectToRoute('app_dette');
                }
            }
        }

        // If errors exist, re-render the form with errors
        if (!empty($errors)) {
            
            
            return $this->render('dette/index.html.twig', [
                'articles' => $articles,
                'dettes' => $dettes,
                'nbrePages' => $nbrePages,
                'page' => $page,
                'nbrePages1' => $nbrePages1,
                'page1' => $page1,
                'montantTotal' => $montantTotal,
                'montantVerserTotal' => $montantVerserTotal,
                'montantRestantTotal' => $montantRestantTotal,
                'form' => $form->createView(),
                'errors' => $errors,
                'showModal' => true,
            ]);
        }
    }

    // Render initial page
    return $this->render('dette/index.html.twig', [
        'articles' => $articles,
        'dettes' => $dettes,
        'nbrePages' => $nbrePages,
        'page' => $page,
        'nbrePages1' => $nbrePages1,
        'page1' => $page1,
        'montantTotal' => $montantTotal,
        'montantVerserTotal' => $montantVerserTotal,
        'montantRestantTotal' => $montantRestantTotal,
        'form' => $form->createView(),
        'errors' => $errors,
        'showModal' => false,
    ]);
}

#[Route('/dette/{id}/archive', name: 'app_dette_archive')]
public function archiveDette(int $id, DetteRepository $detteRepository, EntityManagerInterface $entityManager): Response
{
    $dette = $detteRepository->find($id);

    if (!$dette) {
        $this->addFlash('error', 'Cette dette n\'existe pas.');
        return $this->redirectToRoute('app_dettes');
    }

    // Vérifier si la dette est soldée
    if ($dette->getMontantRestant() > 0) {
        $this->addFlash('error', 'Impossible d\'archiver une dette non soldée.');
        return $this->redirectToRoute('app_dettes');
    }

    // Archiver la dette
    $dette->setIsArchived(true);
    $entityManager->persist($dette);
    $entityManager->flush();

    $this->addFlash('success', 'Dette archivée avec succès.');
    return $this->redirectToRoute('app_dette');
}




//     #[Route('/dette/non-soldes', name: 'app_dette_non_soldes')]
// public function nonSoldesDettes(DetteRepository $detteRepository, Request $request): Response
// {
//     $page = $request->query->getInt('page', 1);
//     $limit = 5;

//     // Rechercher les dettes non soldées (montant restant > 0 et solde = 0)
//     $dettes = $detteRepository->findNonSoldesDettes($page, $limit);

//     $count = $dettes->count();
//     $nbrePages = ceil($count / $limit);

//     // Calcul des totaux
//     $montantTotal = 0;
//     $montantVerserTotal = 0;
//     $montantRestantTotal = 0;

//     foreach ($dettes as $dette) {
//         $montantTotal += $dette->getMontant();
//         $montantVerserTotal += $dette->getMontantVerser();
//         $montantRestantTotal += $dette->getMontantRestant();
//     }

//     return $this->render('dette/index.html.twig', [
//         'dettes' => $dettes,
//         'nbrePages' => $nbrePages,
//         'page' => $page,
//         'montantTotal' => $montantTotal,
//         'montantVerserTotal' => $montantVerserTotal,
//         'montantRestantTotal' => $montantRestantTotal,
//     ]);
// }
#[Route('/dette/non-soldes', name: 'app_dette_non_soldes')]
public function nonSoldesDettes(
    DetteRepository $detteRepository,
    EntityManagerInterface $entityManager,
    ArticleRepository $articleRepository,
    ValidatorInterface $validator,
    Request $request
): Response {
    // Pagination configuration
    $page = $request->query->getInt('page', 1);
    $limit = 5;
    $page1 = $request->query->getInt('page1', 1);
    $limit1 = 2;

    // Fetch data for dettes and articles

    // $dettes = $detteRepository->findNonSoldesDettes($page, $limit);
    $dettes = $detteRepository->findDettesByStatus($page, $limit, false);
    $articles = $articleRepository->findByStatut("DIS", $page1, $limit1);

    // Calculate pagination counts
    $nbrePages = (int) ceil($dettes->count() / $limit);
    $nbrePages1 = (int) ceil($articles->count() / $limit1);

    // Calculate totals for dettes
    $Alldettes = $detteRepository->findNonSoldesDettes();
    $montantTotal = $montantVerserTotal = $montantRestantTotal = 0;
    foreach ($Alldettes as $dette) {
        $montantTotal += $dette->getMontant();
        $montantVerserTotal += $dette->getMontantVerser();
        $montantRestantTotal += $dette->getMontantRestant();
    }

    // Create and handle form
    $dette = new Dette();
    $form = $this->createForm(DetteType::class, $dette);
    $form->handleRequest($request);

    $errors = []; // Error container for displaying issues

    if ($form->isSubmitted()) {
        if (!$form->isValid()) {
            foreach ($form->getErrors(true, true) as $error) {
                $errors[] = $error->getMessage();
            }
        } else {
            // Handle custom validation for selected articles
            $articlesData = $request->request->get('selected_articles');
            $selectedArticles = json_decode($articlesData, true);

            if (empty($selectedArticles)) {
                $errors[] = 'Aucun article sélectionné.';
            } else {
                $totalMontant = 0;

                foreach ($selectedArticles as $articleData) {
                    $article = $articleRepository->find($articleData['id']);
                    $quantity = (int) $articleData['quantity'];

                    if (!$article || $quantity <= 0) {
                        $errors[] = "L'article sélectionné est invalide.";
                        continue;
                    }

                    if ($article->getQteStock() < $quantity) {
                        $errors[] = "La quantité demandée pour l'article {$article->getNomArticle()} dépasse le stock disponible.";
                    } else {
                        $article->setQteStock($article->getQteStock() - $quantity);
                        $detteArticle = new Dette_Article();
                        $detteArticle->setArticle($article);
                        $detteArticle->setQte($quantity);
                        $detteArticle->setDette($dette);
                        $entityManager->persist($detteArticle);

                        $totalMontant += $quantity * $article->getPrix();
                    }
                }

                if (empty($errors)) {
                    $montantVerser = $form->get('montantVerser')->getData();
                    $montantRestant = $totalMontant - $montantVerser;

                    if ($montantRestant < 0) {
                        $errors[] = 'Le montant versé ne peut pas dépasser le montant total.';
                    } else {
                        $dette->setMontant($totalMontant);
                        $dette->setMontantRestant($montantRestant);
                        $dette->setDateAt(new \DateTimeImmutable());

                        // Create payment for the montant versé
                        $payment = new Payment();
                        $payment->setMontant($montantVerser);
                        $payment->setDette($dette);
                        $payment->setDateAt(new \DateTimeImmutable());
                        $entityManager->persist($payment);

                        $entityManager->persist($dette);
                        $entityManager->flush();

                        $this->addFlash('success', 'Dette créée avec succès.');
                        return $this->redirectToRoute('app_dette_non_soldes');
                    }
                }
            }
        }

        // If there are errors, re-render the form with error messages
        if (!empty($errors)) {
            return $this->render('dette/index.html.twig', [
                'dettes' => $dettes,
                'articles' => $articles,
                'nbrePages' => $nbrePages,
                'page' => $page,
                'nbrePages1' => $nbrePages1,
                'page1' => $page1,
                'montantTotal' => $montantTotal,
                'montantVerserTotal' => $montantVerserTotal,
                'montantRestantTotal' => $montantRestantTotal,
                'form' => $form->createView(),
                'errors' => $errors,
                'showModal' => true,
            ]);
        }
    }

    // Render initial page
    return $this->render('dette/index.html.twig', [
        'dettes' => $dettes,
        'articles' => $articles,
        'nbrePages' => $nbrePages,
        'page' => $page,
        'nbrePages1' => $nbrePages1,
        'page1' => $page1,
        'montantTotal' => $montantTotal,
        'montantVerserTotal' => $montantVerserTotal,
        'montantRestantTotal' => $montantRestantTotal,
        'form' => $form->createView(),
        'errors' => [],
        'showModal' => false,
    ]);
}




// #[Route('/dette/soldes', name: 'app_dette_soldes')]
// public function SoldesDettes(DetteRepository $detteRepository, Request $request): Response
// {
//     $page = $request->query->getInt('page', 1);
//     $limit = 5;

//     // Rechercher les dettes non soldées (montant restant > 0 et solde = 0)
//     $dettes = $detteRepository->findSoldesDettes($page, $limit);

//     $count = $dettes->count();
//     $nbrePages = ceil($count / $limit);

//     // Calcul des totaux
//     $montantTotal = 0;
//     $montantVerserTotal = 0;
//     $montantRestantTotal = 0;

//     foreach ($dettes as $dette) {
//         $montantTotal += $dette->getMontant();
//         $montantVerserTotal += $dette->getMontantVerser();
//         $montantRestantTotal += $dette->getMontantRestant();
//     }

//     return $this->render('dette/index.html.twig', [
//         'dettes' => $dettes,
//         'nbrePages' => $nbrePages,
//         'page' => $page,
//         'montantTotal' => $montantTotal,
//         'montantVerserTotal' => $montantVerserTotal,
//         'montantRestantTotal' => $montantRestantTotal,
//     ]);
// }




#[Route('/dette/soldes', name: 'app_dette_soldes')]
public function SoldesDettes(
    DetteRepository $detteRepository,
    EntityManagerInterface $entityManager,
    ArticleRepository $articleRepository,
    Request $request
): Response {
    $page = $request->query->getInt('page', 1);
    $limit = 5;
    $page1 = $request->query->getInt('page1', 1);
    $limit1 = 2;

   
    $dettes = $detteRepository->findDettesByStatus($page, $limit, true);
    $articles = $articleRepository->findByStatut("DIS", $page1, $limit1);

    $nbrePages = (int) ceil($dettes->count() / $limit);
    $nbrePages1 = (int) ceil($articles->count() / $limit1);

    $Alldettes = $detteRepository->findSoldesDettes();
    $montantTotal = $montantVerserTotal = $montantRestantTotal = 0;
    foreach ($Alldettes as $dette) {
        $montantTotal += $dette->getMontant();
        $montantVerserTotal += $dette->getMontantVerser();
        $montantRestantTotal += $dette->getMontantRestant();
    }

    $dette = new Dette();
    $form = $this->createForm(DetteType::class, $dette);
    $form->handleRequest($request);

    $errors = []; 

    if ($form->isSubmitted()) {
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
                    $quantity = (int) $articleData['quantity'];

                    if (!$article || $quantity <= 0) {
                        $errors[] = "L'article sélectionné est invalide.";
                        continue;
                    }

                    if ($article->getQteStock() < $quantity) {
                        $errors[] = "La quantité demandée pour l'article {$article->getNomArticle()} dépasse le stock disponible.";
                    } else {
                        $article->setQteStock($article->getQteStock() - $quantity);
                        $detteArticle = new Dette_Article();
                        $detteArticle->setArticle($article);
                        $detteArticle->setQte($quantity);
                        $detteArticle->setDette($dette);
                        $entityManager->persist($detteArticle);

                        $totalMontant += $quantity * $article->getPrix();
                    }
                }

                if (empty($errors)) {
                    $montantVerser = $form->get('montantVerser')->getData();
                    $montantRestant = $totalMontant - $montantVerser;

                    if ($montantRestant < 0) {
                        $errors[] = 'Le montant versé ne peut pas dépasser le montant total.';
                    } else {
                        $dette->setMontant($totalMontant);
                        $dette->setMontantRestant($montantRestant);
                        $dette->setDateAt(new \DateTimeImmutable());

                        // Create payment for the montant versé
                        $payment = new Payment();
                        $payment->setMontant($montantVerser);
                        $payment->setDette($dette);
                        $payment->setDateAt(new \DateTimeImmutable());
                        $entityManager->persist($payment);

                        $entityManager->persist($dette);
                        $entityManager->flush();

                        $this->addFlash('success', 'Dette créée avec succès.');
                        return $this->redirectToRoute('app_dette_non_soldes');
                    }
                }
            }
        }

        if (!empty($errors)) {
            return $this->render('dette/index.html.twig', [
                'dettes' => $dettes,
                'articles' => $articles,
                'nbrePages' => $nbrePages,
                'page' => $page,
                'nbrePages1' => $nbrePages1,
                'page1' => $page1,
                'montantTotal' => $montantTotal,
                'montantVerserTotal' => $montantVerserTotal,
                'montantRestantTotal' => $montantRestantTotal,
                'form' => $form->createView(),
                'errors' => $errors,
                'showModal' => true,
            ]);
        }
    }

    return $this->render('dette/index.html.twig', [
        'dettes' => $dettes,
        'articles' => $articles,
        'nbrePages' => $nbrePages,
        'page' => $page,
        'nbrePages1' => $nbrePages1,
        'page1' => $page1,
        'montantTotal' => $montantTotal,
        'montantVerserTotal' => $montantVerserTotal,
        'montantRestantTotal' => $montantRestantTotal,
        'form' => $form->createView(),
        'errors' => [],
        'showModal' => false,
    ]);
}



}