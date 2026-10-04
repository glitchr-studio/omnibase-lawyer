<?php

namespace Base\Lawyer\Admin\Settings;

use Base\Admin\Settings\SettingsSectionInterface;
use Base\Lawyer\Service\Record;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The settings' lawyers' section: what the legal notice and the fees page
 * print and only the firm knows - its denomination, its structure of
 * practice, its bar, its network, the day the site was declared to the
 * conseil de l'Ordre, how its fees are set. The compliance widget reads
 * the same.
 */
#[AsTaggedItem(priority: 40)]
final class LawyerSettingsSection implements SettingsSectionInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function getPage(): string
    {
        return self::SETTINGS;
    }

    public function getFields(): array
    {
        $label = fn (string $key): string => $this->translator->trans('settings.'.$key, [], 'lawyer');

        return [
            Record::FIRM => ['required' => false, 'label' => $label('firm')],
            Record::STRUCTURE => ['required' => false, 'label' => $label('structure')],
            Record::BAR => ['required' => false, 'label' => $label('bar')],
            Record::NETWORK => ['required' => false, 'label' => $label('network')],
            Record::DECLARED_AT => ['required' => false, 'label' => $label('declared_at')],
            Record::FEES => ['required' => false, 'form_type' => TextareaType::class, 'label' => $label('fees')],
            Record::FIRST_MEETING => ['required' => false, 'label' => $label('first_meeting')],
            Record::LEGAL_AID => ['required' => false, 'label' => $label('legal_aid')],
        ];
    }
}
