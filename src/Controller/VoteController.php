<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class VoteController extends AbstractController
{
    #[Route('/vote', name: 'vote')]
    public function vote(Request $request): Response
    {
        $session = $request->getSession();
        $questions = $session->get('vragen', []);

        if (!$session->has('waarden')) {
            $session->set('waarden', []);
        }

        if (!$session->has('stemmen')) {//! zorgt dat aantal stemmen begint op nul en stopt het in array
            $session->set('stemmen', array_fill_keys($session->get('waarden'), 0));
        }
        
        //zorgt dat gebruiker kan zien waarop hij kan stemmen
        if ($request->isMethod('POST')) {
            $vote = $request->request->get('vote');
            $stemmen = $session->get('stemmen');
        
            if (array_key_exists($vote, $stemmen)) {
                $stemmen[$vote]++;
                $session->set('stemmen', $stemmen);
            }
        }     
  
        return $this->render('vote/index.html.twig', [
            'waarden' => $session->get('waarden'),
            'vragen' => $questions,
        ]);
    }
    
    //! verstuurt gegevens naar javascript voor de fetch zodat ze worden opgehaald in de chart
    #[Route('/result', name: 'result')]
    public function result(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $stemmen = $session->get('stemmen', []);

        return new JsonResponse($stemmen);
    }
    // niet veel speciaals..
    #[Route('/reset', name: 'reset')]
    public function reset(Request $request): Response
    {
        $session = $request->getSession();
        $waarden = $session->get('waarden', []);
        $stemmen = array_fill_keys($waarden, 0);
        $session->set('stemmen', $stemmen);

        return new Response('Reset succesvol');
    }
}

