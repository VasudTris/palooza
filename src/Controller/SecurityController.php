<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_inlog')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        //! als gebruiker al ingelogd is dan word hij naar home gestuurt
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }
     
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): response
    {
        return $this->redirectToRoute('app_login');
    }
}
