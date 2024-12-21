<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Form\UserType;
use App\Form\AssociateAccountType;
use App\Repository\UserRepository;
use App\Repository\DetteRepository;
use App\Repository\ClientRepository;
use App\Repository\ArticleRepository;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
 

#[Route('/user', name: 'app_user')]
public function index(ClientRepository $clientRepository, UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager): Response
{
    $status = $request->query->get('status');
    $page = $request->query->getInt('page', 1);
    $limit = 5;


    
    $allClients = $clientRepository->findAll();
    $allUsers = $userRepository->findAll();

    if ($status === 'active') {
        $allClients = array_filter($allClients, fn($client) => $client->getUser() && $client->getUser()->isActive());
        $allUsers = array_filter($allUsers, fn($user) => $user->isActive());
    } elseif ($status === 'inactive') {
        $allClients = array_filter($allClients, fn($client) => $client->getUser() && !$client->getUser()->isActive());
        $allUsers = array_filter($allUsers, fn($user) => !$user->isActive());
    }

   
    

    $allResults = array_merge($allClients, $allUsers);

    $totalCount = count($allResults);
    $nbrePages = ceil($totalCount / $limit);

    $paginatedResults = array_slice($allResults, ($page - 1) * $limit, $limit);

    $errors = [];

   

    $id = $request->query->get('id'); 

    $user = null;
    $showModal = false;
    $showAssociateModal = false;
    
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);
    

    $formAssociate = $this->createForm(AssociateAccountType::class);
     $formAssociate->handleRequest($request);

     if ($id) {
        $user = $userRepository->find($id);
        
       
        $showModal = true; 
        
    } else {
        $user = new User();
    }
   
    if ($form->isSubmitted()) {

        
        if ($form->isValid()) {
            $existingUser = $userRepository->findOneBy(['login' => $form->get('login')->getData()]);

        if ($existingUser && $existingUser->getId() !== $user->getId()) {
            $errors[] = 'Le login "' . $form->get('login')->getData() . '" est déjà utilisé.';
        } else {
            $user->setNom($form->get('nom')->getData());
            $user->setPrenom($form->get('prenom')->getData());
            $user->setLogin($form->get('login')->getData());
            $user->setRoles($form->get('roles')->getData());
            $user->setPassword(password_hash($form->get('password')->getData(), PASSWORD_BCRYPT));
            $entityManager->persist($user);
            
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé/modifié avec succès.');
            return $this->redirectToRoute('app_user');
        }
        }
        $showModal = true;
        
    }



    if ($formAssociate->isSubmitted()) {
        if ($formAssociate->isValid()) {
            $clientId = $formAssociate->get('clientId')->getData();
            $nom = $formAssociate->get('nom')->getData();
            $prenom = $formAssociate->get('prenom')->getData();
            $login = $formAssociate->get('login')->getData();
            $password = $formAssociate->get('password')->getData();
            $clientId = $formAssociate->get('clientId')->getData();
    
            $client = $entityManager->getRepository(Client::class)->find($clientId);
    
            if ($client) {
                if ($client->getUser()) {
                    $this->addFlash('error', 'Ce client a déjà un compte utilisateur associé.');
                } else {
                    $existingUser = $entityManager->getRepository(User::class)->findOneBy(['login' => $login]);

                    if ($existingUser) {
                        throw new \Exception('Ce login existe déjà. Veuillez en choisir un autre.');
                    }
                    
                    $user = new User();
                    $user->setNom($nom);
                    $user->setPrenom($prenom);
                    $user->setLogin($login);
                    $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
                    $user->setRoles(['ROLE_CLIENT']);

                    
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $client->setUser($user);
                    $entityManager->persist($client);
                    $entityManager->flush();
    
                    $this->addFlash('success', 'Compte utilisateur associé avec succès.');
                    return $this->redirectToRoute('app_user');
                }
            } else {
                $this->addFlash('error', 'Client introuvable.');
            }
        }
        $showAssociateModal = true;
        
    }
            

    return $this->render('user/index.html.twig', [
        'results' => $paginatedResults,
        'nbrePages' => $nbrePages,
        'page' => $page,
        'form' => $form->createView(),
        'errors' => $errors,
        'formAssociate' => $formAssociate->createView(),
        'showModal' => $showModal,
        'showAssociateModal' => $showAssociateModal,
        

    ]);
}





#[Route('/user/admins', name: 'app_user_admins')]
public function admins(
    UserRepository $userRepository,
    Request $request,
    EntityManagerInterface $entityManager
): Response {
    $status = $request->query->get('status');
    $page = $request->query->getInt('page', 1);
    $limit = 5;

    $users = $userRepository->findByRole('ROLE_ADMIN', $page, $limit, $status);


    $count = $userRepository->countByRole('ROLE_ADMIN', $status);

    $nbrePages = ceil($count / $limit);
    $showModal = false;
    $showAssociateModal = false;

    $user = null;
    $id = $request->query->get('id');
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    $formAssociate = $this->createForm(AssociateAccountType::class);
     $formAssociate->handleRequest($request);
     if ($id) {
        $user = $userRepository->find($id);
        
       
        $showModal = true; 
        
    } else {
        $user = new User();
    }

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            $user->setNom($form->get('nom')->getData());
            $user->setPrenom($form->get('prenom')->getData());
            $user->setLogin($form->get('login')->getData());
            $user->setRoles($form->get('roles')->getData());
            $user->setPassword(password_hash($form->get('password')->getData(), PASSWORD_BCRYPT));
            
            $entityManager->persist($user);
            
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé/modifié avec succès.');
            return $this->redirectToRoute('app_user_admins');
        }

        $showModal = true;
    }
    if ($formAssociate->isSubmitted()) {
            if ($formAssociate->isValid()) {
                $clientId = $formAssociate->get('clientId')->getData();
                $nom = $formAssociate->get('nom')->getData();
                $prenom = $formAssociate->get('prenom')->getData();
                $login = $formAssociate->get('login')->getData();
                $password = $formAssociate->get('password')->getData();
                $clientId = $formAssociate->get('clientId')->getData();
        
                $client = $entityManager->getRepository(Client::class)->find($clientId);
        
                if ($client) {
                    if ($client->getUser()) {
                        $this->addFlash('error', 'Ce client a déjà un compte utilisateur associé.');
                    } else {
                        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['login' => $login]);
    
                        if ($existingUser) {
                            throw new \Exception('Ce login existe déjà. Veuillez en choisir un autre.');
                        }
                        
                        $user = new User();
                        $user->setNom($nom);
                        $user->setPrenom($prenom);
                        $user->setLogin($login);
                        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
                        $user->setRoles(['ROLE_CLIENT']);
    
                        
                        $entityManager->persist($user);
                        $entityManager->flush();
                        $client->setUser($user);
                        $entityManager->persist($client);
                        $entityManager->flush();
        
                        $this->addFlash('success', 'Compte utilisateur associé avec succès.');
                        return $this->redirectToRoute('app_user');
                    }
                } else {
                    $this->addFlash('error', 'Client introuvable.');
                }
            }
            $showAssociateModal = true;
            
        }

    return $this->render('user/index.html.twig', [
        'results' => $users,
        'nbrePages' => $nbrePages,
        'page' => $page,
        'filter' => 'ROLE_ADMIN',
        'form' => $form->createView(),
        'formAssociate' => $formAssociate->createView(),
        'showModal' => $showModal,
        'showAssociateModal' => $showAssociateModal,
    ]);
}




#[Route('/user/boutiquiers', name: 'app_user_boutiquiers')]
public function boutiquiers(
    UserRepository $userRepository,
    Request $request,
    EntityManagerInterface $entityManager
): Response {
    $status = $request->query->get('status');
    $page = $request->query->getInt('page', 1);
    $limit = 5;

    $users = $userRepository->findByRole('ROLE_BOUTIQIER', $page, $limit, $status);
    $count = $userRepository->countByRole('ROLE_BOUTIQIER', $status);

    $nbrePages = ceil($count / $limit);
    $showModal = false;
    $showAssociateModal = false;

    $user = null;
    $id = $request->query->get('id');
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    $formAssociate = $this->createForm(AssociateAccountType::class);
     $formAssociate->handleRequest($request);
     if ($id) {
        $user = $userRepository->find($id);
        
       
        $showModal = true; 
        
    } else {
        $user = new User();
    }

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            $user->setNom($form->get('nom')->getData());
            $user->setPrenom($form->get('prenom')->getData());
            $user->setLogin($form->get('login')->getData());
            $user->setRoles($form->get('roles')->getData());
            $user->setPassword(password_hash($form->get('password')->getData(), PASSWORD_BCRYPT));
            
            $entityManager->persist($user);
            
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé/modifié avec succès.');
            return $this->redirectToRoute('app_user_boutiquiers');
        }

        $showModal = true;
    }
    if ($formAssociate->isSubmitted()) {
            if ($formAssociate->isValid()) {
                $clientId = $formAssociate->get('clientId')->getData();
                $nom = $formAssociate->get('nom')->getData();
                $prenom = $formAssociate->get('prenom')->getData();
                $login = $formAssociate->get('login')->getData();
                $password = $formAssociate->get('password')->getData();
                $clientId = $formAssociate->get('clientId')->getData();
        
                $client = $entityManager->getRepository(Client::class)->find($clientId);
        
                if ($client) {
                    if ($client->getUser()) {
                        $this->addFlash('error', 'Ce client a déjà un compte utilisateur associé.');
                    } else {
                        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['login' => $login]);
    
                        if ($existingUser) {
                            throw new \Exception('Ce login existe déjà. Veuillez en choisir un autre.');
                        }
                        
                        $user = new User();
                        $user->setNom($nom);
                        $user->setPrenom($prenom);
                        $user->setLogin($login);
                        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
                        $user->setRoles(['ROLE_CLIENT']);
    
                        
                        $entityManager->persist($user);
                        $entityManager->flush();
                        $client->setUser($user);
                        $entityManager->persist($client);
                        $entityManager->flush();
        
                        $this->addFlash('success', 'Compte utilisateur associé avec succès.');
                        return $this->redirectToRoute('app_user');
                    }
                } else {
                    $this->addFlash('error', 'Client introuvable.');
                }
            }
            $showAssociateModal = true;
            
        }

    return $this->render('user/index.html.twig', [
        'results' => $users,
        'nbrePages' => $nbrePages,
        'page' => $page,
        'filter' => 'ROLE_BOUTIQIER',
        'form' => $form->createView(),
        'formAssociate' => $formAssociate->createView(),
        'showModal' => $showModal,
        'showAssociateModal' => $showAssociateModal,
    ]);
}


#[Route('/user/clients', name: 'app_user_clients')]
public function clients(
    ClientRepository $clientRepository,
    UserRepository $userRepository,
    Request $request,
    EntityManagerInterface $entityManager
): Response {
    $status = $request->query->get('status');
    $page = $request->query->getInt('page', 1);
    $limit = 5;

    if ($status) {
        $clients = $clientRepository->findAllClientsStatut($page, $limit, $status);
    } else {
        $clients = $clientRepository->findAllClients($page, $limit);
    }
    $clientCount = count($clients);
    $nbrePages = ceil($clientCount / $limit);

    $showModal = false;
    $showAssociateModal = false;

    $user = null;
    $id = $request->query->get('id');
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    $formAssociate = $this->createForm(AssociateAccountType::class);
     $formAssociate->handleRequest($request);
     if ($id) {
        $user = $userRepository->find($id);
        
       
        $showModal = true; 
        
    } else {
        $user = new User();
    }

    $errors = [];
    

    if ($form->isSubmitted()) {

        if ($form->isValid()) {
            $user->setNom($form->get('nom')->getData());
            $user->setPrenom($form->get('prenom')->getData());
            $user->setLogin($form->get('login')->getData());
            $user->setRoles($form->get('roles')->getData());
            $user->setPassword(password_hash($form->get('password')->getData(), PASSWORD_BCRYPT));
            
            $entityManager->persist($user);
            
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé/modifié avec succès.');
            return $this->redirectToRoute('app_user_clients');
        } else {
            foreach ($form->getErrors(true, true) as $error) {
                $errors[] = $error->getMessage();
            }
        }
        $showModal = true;
    }

    if ($formAssociate->isSubmitted()) {
            if ($formAssociate->isValid()) {
                $clientId = $formAssociate->get('clientId')->getData();
                $nom = $formAssociate->get('nom')->getData();
                $prenom = $formAssociate->get('prenom')->getData();
                $login = $formAssociate->get('login')->getData();
                $password = $formAssociate->get('password')->getData();
                $clientId = $formAssociate->get('clientId')->getData();
        
                $client = $entityManager->getRepository(Client::class)->find($clientId);
        
                if ($client) {
                    if ($client->getUser()) {
                        $this->addFlash('error', 'Ce client a déjà un compte utilisateur associé.');
                    } else {
                        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['login' => $login]);
    
                        if ($existingUser) {
                            throw new \Exception('Ce login existe déjà. Veuillez en choisir un autre.');
                        }
                        
                        $user = new User();
                        $user->setNom($nom);
                        $user->setPrenom($prenom);
                        $user->setLogin($login);
                        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
                        $user->setRoles(['ROLE_CLIENT']);
    
                        
                        $entityManager->persist($user);
                        $entityManager->flush();
                        $client->setUser($user);

                        $entityManager->persist($client);
                        $entityManager->flush();
        
                        $this->addFlash('success', 'Compte utilisateur associé avec succès.');
                        return $this->redirectToRoute('app_user');
                    }
                } else {
                    $this->addFlash('error', 'Client introuvable.');
                }
            }
            $showAssociateModal = true;
            
        }


    return $this->render('user/index.html.twig', [
        'results' => $clients,
        'nbrePages' => $nbrePages,
        'page' => $page,
        'filter' => 'ROLE_CLIENT',
        'form' => $form->createView(),
        'showModal' => $showModal,
        'errors' => $errors,
        'formAssociate' => $formAssociate->createView(),
        'showModal' => $showModal,
        'showAssociateModal' => $showAssociateModal,
    ]);
}



#[Route('/dashboard', name: 'app_dashboard')]
public function dashboard(
    DetteRepository $detteRepository,
    ClientRepository $clientRepository,
    ArticleRepository $articleRepository,
    DemandeRepository $demandeRepository,
    Request $request
): Response {
    $totalDettes = $detteRepository->getTotalDettes();

    $totalClients = $clientRepository->getTotalClients();

    $totalArticles = $articleRepository->getQuantiteTotaleArticles();

    $page = $request->query->getInt('page', 1);
    $limit = 5;
    $articles= $articleRepository->findByStatut("RUP",$page, $limit);
    $Count = count($articles);
    $nbrePages = ceil($Count / $limit);
    $demandesEnCours = $demandeRepository->getDemandesEnCours();

    return $this->render('dashboard/index.html.twig', [
        'articles'=> $articles,
        'totalDettes' => $totalDettes,
        'totalClients' => $totalClients,
        'totalArticles' => $totalArticles,
        'demandesEnCours' => $demandesEnCours,
        'nbrePages' => $nbrePages,
        'page' => $page,
    ]);

}


#[Route('/user/{id}/toggle', name: 'app_user_toggle')]
public function toggleStatus(User $user, EntityManagerInterface $em): Response
{
    $user->setIsActive(!$user->isActive());
    $em->persist($user);
    $em->flush();

    $this->addFlash('success', 'Statut de l\'utilisateur mis à jour avec succès.');
    return $this->redirectToRoute('app_user');
}




}
