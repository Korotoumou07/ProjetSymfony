<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route(path: '/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {


        $userConnect=$this->getUser();
        if ($userConnect) {
            $roles=$userConnect->getRoles();
            if (in_array('ROLE_BOUTIQIER',$roles)) {
                return $this->redirectToRoute('app_dashboard'); 
            }
            if (in_array('ROLE_ADMIN',$roles)) {
                return $this->redirectToRoute('app_user'); 
            }
            return $this->redirectToRoute('app_clientDettes');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    #[Route('/access-denied', name: 'app_access_denied')]
    public function accessDenied(): Response
    {
        return $this->render('errors/access_denied.html.twig', [
            'message' => 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.',
        ]);
    }

}
