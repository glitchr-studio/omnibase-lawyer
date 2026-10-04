<?php

namespace Base\Lawyer\Entity;

use Base\Lawyer\Repository\AreaRepository;
use Base\Office\Entity\Member;
use Base\Office\Entity\Office;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A field of practice of the firm (labour, family, criminal...): what it
 * covers, the matters handled, who follows it. Information for the public
 * - never called a "speciality": that word is for the certificates held
 * (Base\Lawyer\Entity\Attorney), and the wording is checked
 * (Base\Lawyer\Compliance\WordingCheck, SpecialisationCheck).
 */
#[ORM\Entity(repositoryClass: AreaRepository::class)]
#[ORM\Table(name: 'lawyer_area')]
class Area
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(length: 160)]
    protected string $name = '';

    #[ORM\Column(length: 160, unique: true)]
    protected string $slug = '';

    /** One or two sentences, for the list. */
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $summary = null;

    /** The page's text: paragraphs separated by an empty line. */
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $body = null;

    /** The matters handled in that field, as a list: "Licenciement", "Rupture conventionnelle"... */
    #[ORM\Column(type: 'json')]
    protected array $matters = [];

    /** @var Collection<int, Member> who of the team follows it */
    #[ORM\ManyToMany(targetEntity: Member::class)]
    #[ORM\JoinTable(name: 'lawyer_area_member')]
    protected Collection $members;

    #[ORM\Column(type: 'boolean')]
    protected bool $active = true;

    #[ORM\Column(type: 'integer')]
    protected int $position = 0;

    public function __construct(string $name = '', ?string $slug = null)
    {
        $this->members = new ArrayCollection();
        $this->name = trim($name);
        $this->slug = Office::slugify($slug ?? $name);
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(?string $name): self { $this->name = trim((string) $name); if ('' === $this->slug) { $this->slug = Office::slugify($this->name); } return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(?string $slug): self { $this->slug = Office::slugify((string) $slug); return $this; }
    public function getSummary(): ?string { return $this->summary; }
    public function setSummary(?string $summary): self { $this->summary = $summary ?: null; return $this; }
    public function getBody(): ?string { return $this->body; }
    public function setBody(?string $body): self { $this->body = $body ?: null; return $this; }
    /** @return list<string> */
    public function getMatters(): array { return $this->matters; }
    public function setMatters(?array $matters): self { $this->matters = array_values(array_filter(array_map('trim', (array) $matters))); return $this; }
    /** The matters, one a line: what the back office's form edits. */
    public function getMattersText(): string { return implode("\n", $this->matters); }
    public function setMattersText(?string $text): self { return $this->setMatters(preg_split('/\R/', (string) $text) ?: []); }
    /** @return Collection<int, Member> */
    public function getMembers(): Collection { return $this->members; }
    public function addMember(Member $member): self { if (!$this->members->contains($member)) { $this->members->add($member); } return $this; }
    public function removeMember(Member $member): self { $this->members->removeElement($member); return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): self { $this->active = $active; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(?int $position): self { $this->position = (int) $position; return $this; }

    /** @return list<string> the body's paragraphs */
    public function getParagraphs(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', (string) $this->body) ?: [])));
    }

    /** Everything written for the public, for the wording check. */
    public function getPublicText(): string
    {
        return implode("\n", array_filter([$this->name, $this->summary, $this->body, implode("\n", $this->matters)]));
    }
}
