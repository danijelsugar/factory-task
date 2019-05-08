<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\IngredientsRepository")
 */
class Ingredients
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\IngredientsTranslation", mappedBy="ingredient")
     */
    private $ingredientsTranslations;

    public function __construct()
    {
        $this->ingredientsTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|IngredientsTranslation[]
     */
    public function getIngredientsTranslations(): Collection
    {
        return $this->ingredientsTranslations;
    }

    public function addIngredientsTranslation(IngredientsTranslation $ingredientsTranslation): self
    {
        if (!$this->ingredientsTranslations->contains($ingredientsTranslation)) {
            $this->ingredientsTranslations[] = $ingredientsTranslation;
            $ingredientsTranslation->setIngredient($this);
        }

        return $this;
    }

    public function removeIngredientsTranslation(IngredientsTranslation $ingredientsTranslation): self
    {
        if ($this->ingredientsTranslations->contains($ingredientsTranslation)) {
            $this->ingredientsTranslations->removeElement($ingredientsTranslation);
            // set the owning side to null (unless already changed)
            if ($ingredientsTranslation->getIngredient() === $this) {
                $ingredientsTranslation->setIngredient(null);
            }
        }

        return $this;
    }
}
