<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="p6_status")
 * @ORM\Entity(repositoryClass="App\Repository\StatusRepository")
 */
class Status
{
    public const ADMIN = 'Administrateur';
    public const CONTRIB = 'Contributeur';
    public const USER = 'Utilisateur';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     * @var string
     */
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
