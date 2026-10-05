<?php

namespace Base\Lawyer\Entity;

use Base\Lawyer\Repository\AreaRepository;
use Base\Office\Entity\Member;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A field of practice of the firm (labour, family, criminal...): what it
 * covers, the matters handled, who follows it. Information for the public
 * - never called a "speciality": that word is for the certificates held
 * (Base\Lawyer\Entity\Attorney), and the wording is checked
 * (Base\Lawyer\Compliance\WordingCheck, SpecialisationCheck).
 *
 * The fields and the methods common to the regimes are omnibase/office's
 * (Base\Office\Entity\Area); the matters and the table are the lawyers'.
 */
#[ORM\Entity(repositoryClass: AreaRepository::class)]
#[ORM\Table(name: 'lawyer_area')]
class Area extends \Base\Office\Entity\Area
{
    /** The matters handled in that field, as a list: "Licenciement", "Rupture conventionnelle"... */
    #[ORM\Column(type: 'json')]
    protected array $matters = [];

    /** @var Collection<int, Member> who of the team follows it */
    #[ORM\ManyToMany(targetEntity: Member::class)]
    #[ORM\JoinTable(name: 'lawyer_area_member')]
    protected Collection $members;

    /** @return list<string> */
    public function getMatters(): array { return $this->matters; }
    public function setMatters(?array $matters): self { $this->matters = self::lines($matters); return $this; }
    /** The matters, one a line: what the back office's form edits. */
    public function getMattersText(): string { return $this->getItemsText(); }
    public function setMattersText(?string $text): self { return $this->setItemsText($text); }

    /** What omnibase/office's Area calls the list. */
    public function getItems(): array { return $this->matters; }
    public function setItems(?array $items): static { return $this->setMatters($items); }
}
