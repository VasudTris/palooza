<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $errors = [];
        //!test request-> verwijderd tussen get en $request
        if ($request->isMethod('POST')) {
            //pakt de ingevulde gegevens op
            $username = $request->get('username');
            $email = $request->get('email');
            $password = $request->get('password');
            $confirmPassword = $request->get('confirm_password');

            if (empty($username)) {
                $errors['username'] = 'Gebruikersnaam is verplicht.';
            }
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Voer een geldig e-mailadres in.';
            }
            if (empty($password)) {
                $errors['password'] = 'Wachtwoord is verplicht.';
            }
            if ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Wachtwoorden komen niet overeen.';
            }
            
            //! als er geen errors zijn dan word account toegevoegd
            if (empty($errors)) {
                $user = new User();
                $user->setUsername($username);
                $user->setEmail($email);
                $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                //!geeft de aangemaakte account de role
                $user->setRoles(['ROLE_USER']);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $this->redirectToRoute('home');
            }
        }

        return $this->render('security/addAcc.html.twig', [
            'errors' => $errors,
        ]);
    }
}
