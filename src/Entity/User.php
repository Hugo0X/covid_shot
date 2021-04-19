<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="UserEntity.unique_entity")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank(message="UserEntity.not_blank")
     * @Assert\Email
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "UserEntity.minLenght",
     *      maxMessage = "UserEntity.maxLenght",
     *      allowEmptyString = false
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="UserEntity.not_blank")
     * @Assert\Type("string")
     * @Assert\Length(
     *      min = 2,
     *      max = 30,
     *      minMessage = "UserEntity.minLenght",
     *      maxMessage = "UserEntity.maxLenght",
     *      allowEmptyString = false
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="UserEntity.not_blank")
     * @Assert\Type("string")
     * @Assert\Length(
     *      min = 2,
     *      max = 30,
     *      minMessage = "UserEntity.minLenght",
     *      maxMessage = "UserEntity.maxLenght",
     *      allowEmptyString = false
     * )
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank(message="UserEntity.not_blank")
     * @Assert\Type("string")
     * @Assert\Length(
     *      min = 5,
     *      max = 20,
     *      minMessage = "UserEntity.minLenght",
     *      maxMessage = "UserEntity.maxLenght",
     *      allowEmptyString = false
     * )
     */
    private $postCode;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="UserEntity.not_blank")
     * @Assert\Type("integer")
     * @Assert\Length(
     *      min = 9,
     *      max = 9,
     *      minMessage = "UserEntity.minLenght",
     *      maxMessage = "UserEntity.maxLenght",
     *      allowEmptyString = false
     * )
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="UserEntity.not_blank")
     * @Assert\Type("string")
     * @Assert\Length(
     *      min = 4,
     *      max = 100,
     *      minMessage = "UserEntity.minLenght",
     *      maxMessage = "UserEntity.maxLenght",
     *      allowEmptyString = false
     * )
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="UserEntity.not_blank")
     * @Assert\Type("string")
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "UserEntity.minLenght",
     *      maxMessage = "UserEntity.maxLenght",
     *      allowEmptyString = false
     * )
     */
    private $city;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="UserEntity.not_blank")
     * @Assert\Type("integer")
     * @Assert\Length(
     *      min = 1,
     *      max = 3,
     *      minMessage = "UserEntity.minLenght",
     *      maxMessage = "UserEntity.maxLenght",
     *      allowEmptyString = false
     * )
     * @Assert\Range(
     *      min = 18,
     *      max = 120,
     *      notInRangeMessage = "UserEntity.range" 
     * )
     */
    private $age;

    /**
     * @ORM\Column(type="string", length=20)
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="UserEntity.not_blank")
     * @Assert\Type("string")
     * @Assert\Length(
     *      min = 7,
     *      max = 8,
     *      minMessage = "UserEntity.minLenght",
     *      maxMessage = "UserEntity.maxLenght",
     *      allowEmptyString = false
     * )
     */
    private $genre;  // nom à modifier

    /**
     * @ORM\Column(type="boolean")
     */
    private $isMedicalStaff;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasComorbidity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPostCode(): ?string
    {
        return $this->postCode;
    }

    public function setPostCode(string $postCode): self
    {
        $this->postCode = $postCode;

        return $this;
    }

    public function getPhone(): ?int
    {
        return $this->phone;
    }

    public function setPhone(int $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getIsMedicalStaff(): ?bool
    {
        return $this->isMedicalStaff;
    }

    public function setIsMedicalStaff(bool $isMedicalStaff): self
    {
        $this->isMedicalStaff = $isMedicalStaff;

        return $this;
    }

    public function getHasComorbidity(): ?bool
    {
        return $this->hasComorbidity;
    }

    public function setHasComorbidity(bool $hasComorbidity): self
    {
        $this->hasComorbidity = $hasComorbidity;

        return $this;
    }
}
