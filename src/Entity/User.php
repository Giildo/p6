<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="p6_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     * @var string
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=128)
     * @var string
     */
    private $password;

    /**
     * @ORM\Column(name="first_name", type="string", length=40)
     * @var string
     */
    private $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=40)
     * @var string
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     * @var string
     */
    private $mail;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @var string
     */
    private $phone;

    /**
     * @ORM\Column(name="mail_validate", type="boolean")
     * @var int
     */
    private $mailValidate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Status")
     * @ORM\JoinColumn(nullable=false)
     * @var Status
     */
    private $status;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Picture")
     * @ORM\JoinColumn(nullable=true)
     * @var Picture
     */
    private $picture;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMailValidate(): ?bool
    {
        return $this->mailValidate;
    }

    public function setMailValidate(bool $mailValidate): self
    {
        $this->mailValidate = $mailValidate;

        return $this;
    }


    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPicture(): ?Picture
    {
        return $this->picture;
    }

    public function setPicture(Picture $picture): self
    {
        $this->picture = $picture;

        return $this;
    }
}
