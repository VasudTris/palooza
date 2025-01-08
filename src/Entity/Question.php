<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $question = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'question', cascade: ['persist', 'remove'])]
    private Collection $answers;

    #[ORM\Column(type: 'boolean')]
    private bool $poll = false;

    #[ORM\Column(type: 'boolean')]
    private bool $quiz = false;

    #[ORM\ManyToMany(targetEntity: Modal::class, mappedBy: 'modalQuestions')]
    private Collection $modals;


    public function getModals(): Collection
    {
        return $this->modals;
    }

    public function addModal(Modal $modal): self
    {
        if (!$this->modals->contains($modal)) {
            $this->modals[] = $modal;

            if (!$modal->getModalQuestions()->contains($this)) {
                $modal->addQuestion($this);  // Zorg ervoor dat de inverse relatie ook wordt bijgewerkt
            }

            error_log('Modal added to question: ' . $modal->getId());
        }

        return $this;
    }


    public function removeModal(Modal $modal): self
    {
        if ($this->modals->removeElement($modal)) {
            $modal->removeQuestion($this);
        }

        return $this;
    }



    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->modals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    public function isPoll(): bool
    {
        return $this->poll;
    }

    public function setPoll(bool $poll): self
    {
        $this->poll = $poll;
        return $this;
    }

    public function isQuiz(): bool
    {
        return $this->quiz;
    }

    public function setQuiz(bool $quiz): self
    {
        $this->quiz = $quiz;
        return $this;
    }
}
