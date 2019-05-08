<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MealRepository")
 */
class Meal
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MealTranslation", mappedBy="meal")
     */
    private $mealTranslations;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     */
    private $category;

    public function __construct()
    {
        $this->mealTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|MealTranslation[]
     */
    public function getMealTranslations(): Collection
    {
        return $this->mealTranslations;
    }

    public function addMealTranslation(MealTranslation $mealTranslation): self
    {
        if (!$this->mealTranslations->contains($mealTranslation)) {
            $this->mealTranslations[] = $mealTranslation;
            $mealTranslation->setMeal($this);
        }

        return $this;
    }

    public function removeMealTranslation(MealTranslation $mealTranslation): self
    {
        if ($this->mealTranslations->contains($mealTranslation)) {
            $this->mealTranslations->removeElement($mealTranslation);
            // set the owning side to null (unless already changed)
            if ($mealTranslation->getMeal() === $this) {
                $mealTranslation->setMeal(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
