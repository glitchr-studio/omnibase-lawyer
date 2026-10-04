<?php

namespace Base\Lawyer\Entity;

use Base\Lawyer\Enum\Specialisation;
use Base\Lawyer\Repository\AttorneyRepository;
use Base\Office\Entity\Member;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A member of the team as a lawyer: the bar they are registered with, the
 * year of their oath, their certified specialisations (from the official
 * list, two at most), a specific qualification within one, and their
 * dominant fields of activity - three at most, from an actual and habitual
 * practice (RIN art. 10.2).
 */
#[ORM\Entity(repositoryClass: AttorneyRepository::class)]
#[ORM\Table(name: 'lawyer_attorney')]
class Attorney
{
    /** RIN art. 10.2: « dont le nombre revendiqué ne peut être supérieur à trois ». */
    public const MAX_DOMINANT_FIELDS = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\OneToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected ?Member $member = null;

    /** "Barreau de Lyon": the bar they are registered with (none: the firm's). */
    #[ORM\Column(length: 160, nullable: true)]
    protected ?string $bar = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $swornIn = null;

    /** @var list<string> values of Specialisation: certificates held, not fields of interest */
    #[ORM\Column(type: 'json')]
    #[Assert\Count(max: Specialisation::MAX)]
    protected array $specialisations = [];

    /** A specific qualification obtained within a specialisation, as the certificate words it. */
    #[ORM\Column(length: 200, nullable: true)]
    protected ?string $qualification = null;

    /** @var list<string> dominant fields of activity, in the lawyer's words */
    #[ORM\Column(type: 'json')]
    #[Assert\Count(max: self::MAX_DOMINANT_FIELDS)]
    protected array $dominantFields = [];

    public function __construct(?Member $member = null, ?string $bar = null)
    {
        $this->member = $member;
        $this->bar = $bar ?: null;
    }

    public function __toString(): string
    {
        return (string) $this->member;
    }

    public function getId(): ?int { return $this->id; }
    public function getMember(): ?Member { return $this->member; }
    public function setMember(?Member $member): self { $this->member = $member; return $this; }
    public function getBar(): ?string { return $this->bar; }
    public function setBar(?string $bar): self { $this->bar = $bar ?: null; return $this; }
    public function getSwornIn(): ?int { return $this->swornIn; }
    public function setSwornIn(?int $year): self { $this->swornIn = $year ?: null; return $this; }
    public function getQualification(): ?string { return $this->qualification; }
    public function setQualification(?string $qualification): self { $this->qualification = $qualification ?: null; return $this; }

    /** @return list<Specialisation> only what the official list knows */
    public function getSpecialisations(): array
    {
        return array_values(array_filter(array_map(static fn ($value) => Specialisation::tryFrom((string) $value), $this->specialisations)));
    }

    /** @return list<string> */
    public function getSpecialisationValues(): array { return $this->specialisations; }

    /** Anything outside the official list is dropped: a specialisation is a certificate, not a wish. */
    public function setSpecialisationValues(?array $values): self
    {
        $kept = [];
        foreach ((array) $values as $value) {
            $case = $value instanceof Specialisation ? $value : Specialisation::tryFrom((string) $value);
            if (null !== $case && !\in_array($case->value, $kept, true)) {
                $kept[] = $case->value;
            }
        }
        $this->specialisations = $kept;

        return $this;
    }

    public function isSpecialist(): bool
    {
        return [] !== $this->getSpecialisations();
    }

    /** @return list<string> */
    public function getDominantFields(): array { return $this->dominantFields; }
    public function setDominantFields(?array $fields): self { $this->dominantFields = array_values(array_unique(array_filter(array_map('trim', (array) $fields)))); return $this; }
    /** One a line: what the back office's form edits. */
    public function getDominantFieldsText(): string { return implode("\n", $this->dominantFields); }
    public function setDominantFieldsText(?string $text): self { return $this->setDominantFields(preg_split('/\R/', (string) $text) ?: []); }
}
