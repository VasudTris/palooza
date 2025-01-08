<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Question;
use App\Entity\Answer;
use App\Entity\Modal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Common\Collections\ArrayCollection;

class HomeController extends AbstractController
{
    #[Route(path: '/home', name: 'home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        //! controleert of de gebruiker ingelogd is
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login');
        }

        //! fomr voor het toevoegen van een vraag
        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('home'))
            ->add('vraag', TextType::class, [
                'label' => 'Voeg vraag toe',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Verstuur',
                'attr' => ['class' => 'submit'],
            ]);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        //! bij submit word de vraag verstuurd naar home
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $questionText = $data['vraag'];

            $vraag = new Question();
            $vraag->setQuestion($questionText);
            $vraag->setUser($this->getUser());

            $entityManager->persist($vraag);
            $entityManager->flush();

            return $this->redirectToRoute('home', [
                'success' => 'Vraag succesvol toegevoegd!',
            ]);
        }

        //! haalt de modals op
        //* eerste manier om iets op te halen per ingelogd gebruiker
        $modals = $entityManager->getRepository(Modal::class)->findBy(['user' => $this->getUser()]);

        //! Zet de PersistentCollection om naar ArrayCollection
        $modalCollection = new ArrayCollection($modals);

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
            //* twede manier om iets op te halen per ingelogd gebruiker
            'vragen' => $entityManager->getRepository(Question::class)->findBy(['user' => $this->getUser()]),
            'modals' => $modalCollection,
        ]);
    }

    #[Route('/save-answers', name: 'save_answers', methods: ['POST'])]
    public function saveAnswers(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $questionId = $data['questionId'] ?? null;
        $answers = $data['answers'] ?? [];
        
        //! stopt post verzoek als de gevesens ongeldig zijn
        if (!$questionId || empty($answers)) {
            return new JsonResponse(['success' => false, 'message' => 'Ongeldige gegevens'], Response::HTTP_BAD_REQUEST);
        }

        //! zoekt de vraag op
        $question = $entityManager->getRepository(Question::class)->find($questionId);
        
        //! check of vraag null is
        if (!$question) {
            return new JsonResponse(['success' => false, 'message' => 'Vraag niet gevonden'], Response::HTTP_NOT_FOUND);
        }

        //! verwijderd bestaande antwoord voor wijzigen
        foreach ($question->getAnswers() as $existingAnswer) {
            $entityManager->remove($existingAnswer);
        }

        //! voor controle of er minstens 1 correct antwoord is
        $correctAnswer = false;

        //! slaat de nieuwe antwoorden op met een controle of er een correcte antwoord is
        foreach ($answers as $answerData) {
            if (!empty($answerData['answer'])) {
                $answer = new Answer();
                $answer->setAnswer($answerData['answer']);
                $answer->setIsCorrect($answerData['isCorrect']);
                if ($answerData['isCorrect']) {
                    $correctAnswer = true;
                }
                $answer->setQuestion($question);
                $entityManager->persist($answer);
            }
        }

        //! stelt de vraag in als quiz of poll
        $question->setQuiz($correctAnswer);
        $question->setPoll(!$correctAnswer);

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/delete-question/{id}', name: 'delete_question', methods: ['DELETE'])]
    public function deleteQuestion($id, EntityManagerInterface $entityManager): JsonResponse
    {
        //! haalt de vraag op via id
        $question = $entityManager->getRepository(Question::class)->find($id);

        if (!$question) {
            return new JsonResponse(['success' => false, 'message' => 'Vraag niet gevonden'], Response::HTTP_NOT_FOUND);
        }

        //! controleert of de vraag van de ingelogde gebruiker is
        if ($question->getUser() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Geen toegang om deze vraag te verwijderen'], Response::HTTP_FORBIDDEN);
        }

        //! verwijdert de vraag en de bijbehorende antwoorden
        $entityManager->remove($question);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Vraag succesvol verwijderd'], Response::HTTP_OK);
    }

    #[Route('/add-modal', name: 'add_modal', methods: ['POST'])]
    public function addModal(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        //! voor het omzetten naar een json response
        $data = json_decode($request->getContent(), true);

        //! haalt de ingelogde gebruiker op
        $user = $this->getUser();

        //! koppelt modal aan ingelogd gebruiker
        $modal = new Modal();
        $modal->setNameModal($data['nameModal'] ?? "");
        $modal->setUser($user);

        $entityManager->persist($modal);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'modalId' => $modal->getId(),
        ]);
    }

    #[Route('/save-modal', name: 'save_modal', methods: ['POST'])]
    public function saveModal(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {   
         //! voor het omzetten naar een json response
        $data = json_decode($request->getContent(), true);
        //! haalt de modal id en naam op
        $modalId = $data['modalId'] ?? null; 
        $modalName = $data['nameModal'] ?? null;

        if (!$modalId || !$modalName) {
            return new JsonResponse(['success' => false, 'message' => 'Ongeldige gegevens'], Response::HTTP_BAD_REQUEST);
        }

        //! haakt modal op
        $modal = $entityManager->getRepository(Modal::class)->find($modalId);

        if (!$modal) {
            return new JsonResponse(['success' => false, 'message' => 'Modal niet gevonden'], Response::HTTP_NOT_FOUND);
        }

        //! update de naam van de modal
        $modal->setNameModal($modalName);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/delete-modal/{id}', name: 'delete_modal', methods: ['DELETE'])]
    public function deleteModal($id, EntityManagerInterface $entityManager): JsonResponse
    {
        //! haalt modal op
        $modal = $entityManager->getRepository(Modal::class)->find($id);

        if (!$modal) {
            return new JsonResponse(['success' => false, 'message' => 'Modal niet gevonden'], Response::HTTP_NOT_FOUND);
        }

        //? Controleert of de modal van de ingelogde gebruiker is
        if ($modal->getUser() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Geen toegang om deze modal te verwijderen'], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($modal);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Modal succesvol verwijderd'], Response::HTTP_OK);
    }

    #[Route('/add-questionToModal', name: 'add-question-to-modal', methods: ['POST'])]
    public function addQuestionToModal(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        //! zet gegevens om naar json response
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid JSON'], 400);
        }
        
        //! haalt vraag id en modal id op
        $questionId = $data['questionId'] ?? null;
        $modalId = $data['modalId'] ?? null;

        if (!$questionId || !$modalId) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing parameters'], 400);
        }
        
        //! zoekt question en modal op basis van id
        $question = $entityManager->getRepository(Question::class)->find($questionId);
        $modal = $entityManager->getRepository(Modal::class)->find($modalId);
        
        //! voegt question aan modal
        if ($question && $modal) {
            $modal->addQuestion($question);
            $entityManager->persist($modal);
            $entityManager->flush();

            return new JsonResponse(['status' => 'success']);
        }
        return new JsonResponse(['status' => 'error', 'message' => 'Vraag of modal niet gevonden'], 404);
    }

    #[Route('/get-modalQuestions/{id}', name: 'get_modal_questions', methods: ['GET'])]
    public function getModalQuestions(EntityManagerInterface $entityManager, $id): JsonResponse
    {
        //!haalt modal op
        $modal = $entityManager->getRepository(Modal::class)->find($id);
        //! lege array voor in de modal
        $questions = [];

        if ($modal) {//! haalt quesitons op als een array
            foreach ($modal->getModalQuestions() as $question) {
                $questions[] = $question->getQuestion(); 
            }
        }
        return new JsonResponse(['questions' => $questions]);
    }
}
