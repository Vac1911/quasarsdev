<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Qis\Orm\Annotation\CMS as CMS;
use Qis\Orm\Entity\Traits\Fillable;
use Qis\Orm\Entity\Traits\Identifiable;
use Qis\Orm\Entity\Traits\Resolvable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Fillable, Identifiable;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected int $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @CMS\Property(required=true)
     */
    protected string $name;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @CMS\Property(required=true)
     */
    protected string $email;

    /**
     * @ORM\Column(type="json")
     * @CMS\Property(editable=false)
     */
    protected array $roles = [];

    /**
     * @var ?string The hashed password
     * @ORM\Column(type="string")
     */
    protected ?string $password;

    /**
     * @var DateTimeImmutable
     *
     * @CMS\AddedField
     * @ORM\Column(name="create_at", type="datetime_immutable", nullable=false)
     * @CMS\Property(editable=false)
     */
    protected DateTimeImmutable $createAt;

    /**
     * @var DateTimeImmutable
     *
     * @CMS\UpdatedField
     * @ORM\Column(name="updated_at", type="datetime_immutable", nullable=false)
     * @CMS\Property(editable=false)
     */
    protected DateTimeImmutable $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="author")
     * @CMS\Property()
     */
    protected $posts;

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
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
}
