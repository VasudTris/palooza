<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class ResultsController extends AbstractController
{
    #[Route('/results', name: 'results')]
    public function index(Request $request, SessionInterface $session, LoggerInterface $logger): Response
    {
        // Check of gebruiker roll user heeft
        if (!$this->isGranted('ROLE_USER')) {
            dump('User is not granted ROLE_USER');
            return $this->redirectToRoute('login');
        }

        $waarden = $session->get('waarden', []);
        $stemmen = $session->get('stemmen', array_fill_keys($waarden, 0));
        $vragen = $session->get('vragen', []);
        // Haal vraag en antwoorden uit de request (indien aanwezig)
        $newQuestion = $request->query->get('vraag');
        $newAnswersJson = $request->query->get('antwoorden');

        // Decodeer de JSON-string naar een array
        $newAnswers = json_decode($newAnswersJson, true);

        // Controleer of het een array is
        if (!is_array($newAnswers)) {
            $newAnswers = [];
        }

        if ($newQuestion && !empty($newAnswers)) {
            // Update de sessie met de nieuwe vraag en antwoorden
            $session->set('vragen', [$newQuestion]);
            $session->set('waarden', $newAnswers);

            // Reset de stemmen voor de nieuwe antwoorden
            $stemmen = array_fill_keys($newAnswers, 0);
            $session->set('stemmen', $stemmen);

            return $this->redirectToRoute('results');
        }

        return $this->render('results/index.html.twig', [
            'waarden' => $waarden,
            'stemmen' => $stemmen,
            'vragen' => $vragen,
        ]);
    }
}
