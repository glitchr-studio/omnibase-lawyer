<?php

namespace Base\Lawyer\Service;

use Base\Service\SettingBagInterface;

/**
 * What only the firm knows and the back office's settings keep: its
 * denomination, its structure of practice, its bar, its network, the day
 * the site was declared to the conseil de l'Ordre, how its fees are set.
 * Read without ever failing a page: a settings table not there yet answers
 * nothing.
 */
class Record
{
    public const FIRM = 'lawyer.firm.name';
    public const STRUCTURE = 'lawyer.firm.structure';
    public const BAR = 'lawyer.firm.bar';
    public const NETWORK = 'lawyer.firm.network';
    public const DECLARED_AT = 'lawyer.order.declared_at';
    public const FEES = 'lawyer.fees.terms';
    public const FIRST_MEETING = 'lawyer.fees.first_meeting';
    public const LEGAL_AID = 'lawyer.fees.legal_aid';

    public function __construct(private readonly SettingBagInterface $settings)
    {
    }

    public function get(string $path): ?string
    {
        try {
            $value = $this->settings->getScalar($path);
        } catch (\Throwable) {
            return null;
        }
        $value = \is_scalar($value) ? trim((string) $value) : '';

        return '' === $value ? null : $value;
    }

    public function getFirm(): ?string { return $this->get(self::FIRM); }
    public function getStructure(): ?string { return $this->get(self::STRUCTURE); }
    public function getBar(): ?string { return $this->get(self::BAR); }
    public function getNetwork(): ?string { return $this->get(self::NETWORK); }
    public function getFees(): ?string { return $this->get(self::FEES); }
    public function getFirstMeeting(): ?string { return $this->get(self::FIRST_MEETING); }
    public function getLegalAid(): ?string { return $this->get(self::LEGAL_AID); }

    /** The day the site was declared to the conseil de l'Ordre, when what was typed is a date. */
    public function getDeclaredAt(): ?\DateTimeImmutable
    {
        $value = $this->get(self::DECLARED_AT);
        if (null === $value) {
            return null;
        }
        foreach (['!Y-m-d', '!d/m/Y', '!d.m.Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if (false !== $date && $date->format(ltrim($format, '!')) === $value) {
                return $date;
            }
        }

        return null;
    }
}
