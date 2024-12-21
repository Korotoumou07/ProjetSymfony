<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Statut;
use App\Entity\Demande;
use App\Entity\Demande_Article;
use App\Form\ClientType;
use App\Form\DemandeType;
use App\Dto\ClientFormSearch;
use App\Form\ClientFormSearchType;
use App\Repository\UserRepository;
use App\Repository\DetteRepository;
use App\Repository\ClientRepository;
use App\Repository\ArticleRepository;
use App\Repository\DemandeRepository;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientController extends AbstractController
{



#[Route('/client', name: 'app_client')]
public function index(ClientRepository $clientRepository,UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
{
    $clientFormSearch = new ClientFormSearch();
    $searchClientForm = $this->createForm(ClientFormSearchType::class, $clientFormSearch);
    $searchClientForm->handleRequest($request);

    $page = $request->query->getInt('page', 1);
    $limit = 10;
    $clients = $clientRepository->findAllClients($page, $limit);
    $count = $clients->count();
    $nbrePages = ceil($count / $limit);

    if ($searchClientForm->isSubmitted() && $searchClientForm->isValid()) {
        $surname = $clientFormSearch->getSurname();
        $telephone = $clientFormSearch->getTelephone();
        $statut = $clientFormSearch->getStatut();

        if ($surname != "") {
            $clients = $clientRepository->findBy(['surname' => $surname]);
        }
        if ($telephone != "") {
            $clients = $clientRepository->findBy(['telephone' => $telephone]);
        }
        if ($statut != "Tout") {
            $clients = $clientRepository->findByClientWithOrUser($statut);
        }
    }

    $client = new Client();
    $form = $this->createForm(ClientType::class, $client);
    $form->handleRequest($request);


    if ($form->isSubmitted()) {
        $errors = $validator->validate($client);

        $login = $form->get('login')->getData();
    $existingUser = $userRepository->findOneBy(['login' => $login]);

    if ($existingUser) {
        $form->get('login')->addError(new FormError('Ce login existe déjà. Veuillez en choisir un autre.'));
    }


        if (count($errors) > 0 || !$form->isValid()) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }

    
            return $this->render('client/index.html.twig', [
                'clients' => $clients,
                'searchClientform' => $searchClientForm->createView(),
                'form' => $form->createView(),
                'nbrePages' => $nbrePages,
                'page' => $page,
                'showModal' => true,
            ]);
        }
        $createAccount = $form->get('createAccount')->getData();

        if ($createAccount) {
            $user = new User();
            $user->setNom($form->get('nom')->getData());
            $user->setPrenom($form->get('prenom')->getData());
            $user->setLogin($form->get('login')->getData());
            $user->setPassword(password_hash($form->get('password')->getData(), PASSWORD_BCRYPT));
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $client->setUser($user);
        }

        $entityManager->persist($client);
        $entityManager->flush();

        $this->addFlash('success', 'Le client a été ajouté avec succès.');
        return $this->redirectToRoute('app_client');
    }

    return $this->render('client/index.html.twig', [
        'clients' => $clients,
        'searchClientform' => $searchClientForm->createView(),
        'form' => $form->createView(),
        'nbrePages' => $nbrePages,
        'page' => $page,
        'showModal' => false,
    ]);
}


#[Route('/client/{id}/dettes', name: 'app_clientdettes')]
public function clientDettes(int $id, DetteRepository $detteRepository, Request $request, ClientRepository $clientRepository): Response
{

    $page = $request->query->getInt('page', 1);
    $limit = 5;
   
    $client = $clientRepository->find($id);

    if (!$client) {
        throw $this->createNotFoundException('Client not found');
    }

    $dettes = $detteRepository->findClientDettes($client, $page, $limit);
    $count = $dettes->count();
    $nbrePages = ceil($count / $limit);
    $dettesArray = iterator_to_array($dettes->getIterator());

    $totalMontant = array_reduce($dettesArray, fn($sum, $d) => $sum + $d->getMontant(), 0);
    $totalMontantVerser = array_reduce($dettesArray, fn($sum, $d) => $sum + $d->getMontantVerser(), 0);
    $totalMontantRestant = array_reduce($dettesArray, fn($sum, $d) => $sum + $d->getMontantRestant(), 0);


    return $this->render('client/dettes.html.twig', [
        'client' => $client,
        'dettes' => $dettes,
        'totalMontant' => $totalMontant,
        'totalMontantVerser' => $totalMontantVerser,
        'totalMontantRestant' => $totalMontantRestant,
        'nbrePages' => $nbrePages,
        'page' => $page,
    ]);
}



#[Route('/clientDettes', name: 'app_clientDettes')]
public function listClientMesDettes(
    DetteRepository $detteRepository,
    UserRepository $userRepository,
    Security $security,
    ClientRepository $clientRepository,
    Request $request
): Response {
    
    $user = $security->getUser();
    $identifier = $user->getUserIdentifier();
    $user = $userRepository->findOneByIdentifier($identifier);
    
    $client = $clientRepository->findByUser($user);
    
    
    $page = $request->query->getInt('page', 1);
    $limit = 4;
    
    $dettes = $detteRepository->findClientDettes($client, $page, $limit);
    $count = $dettes->count();
    $nbrePages = ceil($count / $limit);


    $allDettes = $detteRepository->findAllDettesForClient($client);
    $montantTotal = $montantVerserTotal = $montantRestantTotal = 0;

    foreach ($allDettes as $dette) {
        $montantTotal += $dette->getMontant();
        $montantVerserTotal += $dette->getMontantVerser();
        $montantRestantTotal += $dette->getMontantRestant();
    }

    return $this->render('dette/client_dettes.html.twig', [
        'dettes' => $dettes,
        'page' => $page,
        'nbrePages' => $nbrePages,
        'montantTotal' => $montantTotal,
        'montantVerserTotal' => $montantVerserTotal,
        'montantRestantTotal' => $montantRestantTotal,
    ]);
}

#[Route('/clientDettesNonSoldes', name: 'app_clientDettesNonSoldes')]
public function listClientNonSoldesDettes(
    DetteRepository $detteRepository,
    UserRepository $userRepository,
    ClientRepository $clientRepository,
    Security $security,
    Request $request
): Response {
    $user = $security->getUser();
    $identifier = $user->getUserIdentifier();
    $user = $userRepository->findOneByIdentifier($identifier);
    $client = $clientRepository->findByUser($user);

  
    $page = $request->query->getInt('page', 1);
    $limit = 4;
    $dettes = $detteRepository->findNonSoldesDettesForClient($client, $page, $limit);
    $count = $dettes->count();
    $nbrePages = ceil($count / $limit);

 
    $allDettes = $detteRepository->findAllNonSoldesDettesForClient($client);
    $montantTotal = $montantVerserTotal = $montantRestantTotal = 0;

    foreach ($allDettes as $dette) {
        $montantTotal += $dette->getMontant();
        $montantVerserTotal += $dette->getMontantVerser();
        $montantRestantTotal += $dette->getMontantRestant();
    }

    return $this->render('dette/client_dettes.html.twig', [
        'dettes' => $dettes,
        'page' => $page,
        'nbrePages' => $nbrePages,
        'montantTotal' => $montantTotal,
        'montantVerserTotal' => $montantVerserTotal,
        'montantRestantTotal' => $montantRestantTotal,
    ]);
}

#[Route('/clientDettesSoldes', name: 'app_clientDettesSoldes')]
public function listClientSoldesDettes(
    DetteRepository $detteRepository,
    UserRepository $userRepository,
    ClientRepository $clientRepository,
    Security $security,
    Request $request
): Response {
    $user = $security->getUser();
    $identifier = $user->getUserIdentifier();
    $user = $userRepository->findOneByIdentifier($identifier);
    $client = $clientRepository->findByUser($user);

    $page = $request->query->getInt('page', 1);
    $limit = 4;
    $dettes = $detteRepository->findSoldesDettesForClient($client, $page, $limit);
    $count = $dettes->count();
    $nbrePages = ceil($count / $limit);

    $allDettes = $detteRepository->findAllSoldesDettesForClient($client);
    $montantTotal = $montantVerserTotal = $montantRestantTotal = 0;

    foreach ($allDettes as $dette) {
        $montantTotal += $dette->getMontant();
        $montantVerserTotal += $dette->getMontantVerser();
        $montantRestantTotal += $dette->getMontantRestant();
    }

    return $this->render('dette/client_dettes.html.twig', [
        'dettes' => $dettes,
        'page' => $page,
        'nbrePages' => $nbrePages,
        'montantTotal' => $montantTotal,
        'montantVerserTotal' => $montantVerserTotal,
        'montantRestantTotal' => $montantRestantTotal,
    ]);
}





#[Route('/client/demandes', name: 'app_client_Demande')]
public function listClientMesDemandes(
    DemandeRepository $demandeRepository,
    UserRepository $userRepository,
    ArticleRepository $articleRepository,
    ClientRepository $clientRepository,
    EntityManagerInterface $entityManager,
    Security $security,
    Request $request
): Response {
    
    $user = $security->getUser();
    $identifier = $user->getUserIdentifier();
    $user = $userRepository->findOneByIdentifier($identifier);
    $client = $clientRepository->findByUser($user);
    $page = $request->query->getInt('page', 1);
    $limit = 4;
    $page1 = $request->query->getInt('page1', 1);
    $limit1 = 2;


    $demandes = $demandeRepository->findClientDemandes($client, $page, $limit);
    $count = $demandes->count();
    $nbrePages = ceil($count / $limit);

    $articles = $articleRepository->findDIS($page1, $limit1);

    $count1 = $articles->count();
    $nbrePages1 = ceil($count1 / $limit1);
   

    $demande = new Demande();
    $form = $this->createForm(DemandeType::class, $demande);
    $form->handleRequest($request);

    
   

    $errors = []; 

    if ($form->isSubmitted()) {
       
        if (!$form->isValid()) {
            foreach ($form->getErrors(true, true) as $error) {
                $errors[] = $error->getMessage();
            }

            return $this->render('demande/client_demandes.html.twig', [
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
                $demande->setClient($client);
                $entityManager->persist($demande);
                $entityManager->flush();

                $this->addFlash('success', 'Demande créée avec succès.');
                return $this->redirectToRoute('app_demande');
            }
        }

        if (!empty($errors)) {
            return $this->render('demande/client_demandes.html.twig', [
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

    
    return $this->render('demande/client_demandes.html.twig', [
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

#[Route('/client/demandes/{statut}', name: 'app_client_demandes_statut', requirements: ['statut' => 'ENCOURS|ANNULE|ACCEPTE'])]
public function listClientDemandesByStatut(
    string $statut,
    DemandeRepository $demandeRepository,
    UserRepository $userRepository,
    ClientRepository $clientRepository,
    ArticleRepository $articleRepository,
    EntityManagerInterface $entityManager,
    Security $security,
    Request $request
): Response {
    $user = $security->getUser();
    $identifier = $user->getUserIdentifier();
    $user = $userRepository->findOneByIdentifier($identifier);
    $client = $clientRepository->findByUser($user);

    $page = $request->query->getInt('page', 1);
    $limit = 4;
    $page1 = $request->query->getInt('page1', 1);
    $limit1 = 2;

    $demandes = $demandeRepository->findClientDemandesByStatut($client, $statut, $page, $limit);
    $count = $demandes->count();
    $nbrePages = ceil($count / $limit);

    $articles = $articleRepository->findByStatut("DIS", $page1, $limit1);
    $count1 = $articles->count();
    $nbrePages1 = ceil($count1 / $limit);

    $demande = new Demande();
    $form = $this->createForm(DemandeType::class, $demande);
    $form->handleRequest($request);
    $errors = []; 

    if ($form->isSubmitted()) {
        if (!$form->isValid()) {
            foreach ($form->getErrors(true, true) as $error) {
                $errors[] = $error->getMessage();
            }

            return $this->render('demande/client_demandes.html.twig', [
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
            return $this->render('demande/client_demandes.html.twig', [
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

    
    return $this->render('demande/client_demandes.html.twig', [
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