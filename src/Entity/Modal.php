<?php

namespace App\Entity;

use App\Entity\User; // Voeg de User-entity toe
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Modal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180)]
    private string $nameModal;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;
    
    #[ORM\ManyToMany(targetEntity: Question::class, inversedBy: 'modals')]
    #[ORM\JoinTable(name:"modal_question")]
    private Collection $modalQuestions;

    public function __construct()
    {
        $this->modalQuestions = new ArrayCollection(); // Initialiseer de collectie

    }

    public function addQuestion(Question $question): self
    {
        if (!$this->modalQuestions->contains($question)) {
            $this->modalQuestions[] = $question;
            $question->addModal($this); // Zorg ervoor dat de inverse relatie ook wordt bijgewerkt
            error_log('Question added to modal: ' . $question->getId());
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->modalQuestions->removeElement($question)) {
            $question->removeModal($this); 
        }

        return $this;
    }

    public function getModalQuestions(): Collection
    {
        return $this->modalQuestions;
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameModal(): string
    {
        return $this->nameModal;
    }

    public function setNameModal(string $nameModal): self
    {
        $this->nameModal = $nameModal;

        return $this;
    }


public function setModalQuestions(Collection $modalQuestions): self
{
    $this->modalQuestions = $modalQuestions;

    return $this;
}

}
