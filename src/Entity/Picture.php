<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Table(name="p6_picture")
 * @ORM\Entity(repositoryClass="App\Repository\PictureRepository")
 */
class Picture
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $alt;

    /**
     * @ORM\Column(type="string", length=30)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=5)
     * @var string
     */
    private $ext;

    /**
     * @var UploadedFile
     */
    private $file;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
    }

    public function upload(string $origin)
    {
        if ($this->file === null) {
            return;
        }

        $this->ext = $this->file->guessClientExtension();

        $this->file->move($this->getUploadRootDir($origin), "{$this->name}.{$this->ext}");
    }

    public function getUploadDir(string $origin): string
    {
        return 'img/pic_dl/' . $origin;
    }

    public function getUploadRootDir(string $origin): string
    {
        return __DIR__ . '/../../public/' . $this->getUploadDir($origin);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getExt(): ?string
    {
        return $this->ext;
    }

    public function setExt(?string $ext): self
    {
        $this->ext = $ext;

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }
}
