<?php

namespace Base\Lawyer\Controller\Admin\Crud;

use Base\Admin\Config\Actions;
use Base\Admin\Controller\AbstractCrudController;
use Base\Field\AssociationField;
use Base\Field\IdField;
use Base\Field\IntegerField;
use Base\Field\SelectField;
use Base\Field\TextareaField;
use Base\Field\TextField;
use Base\Lawyer\Entity\Attorney;
use Base\Lawyer\Enum\Specialisation;
use Base\Office\Controller\Admin\OpenToTrait;

/**
 * A member of the team as a lawyer: bar, oath, and what RIN art. 10.2
 * frames - the certified specialisations, picked in the official list (two
 * at most), and the dominant fields (three at most).
 */
class AttorneyCrudController extends AbstractCrudController
{
    use OpenToTrait;

    public static function getEntityFqcn(): string
    {
        return Attorney::class;
    }

    public static function getPreferredIcon(): ?string
    {
        return 'fa-solid fa-scale-balanced';
    }

    public function configureActions(Actions $actions): Actions
    {
        return $this->openTo(parent::configureActions($actions), 'ROLE_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield AssociationField::new('member', '@lawyer.admin.field.member')->setColumns(6);
        yield TextField::new('bar', '@lawyer.admin.field.bar')->setColumns(4)->setRequired(false)->setHelp('@lawyer.admin.help.bar');
        yield IntegerField::new('swornIn', '@lawyer.admin.field.sworn_in')->setColumns(2)->setRequired(false);
        yield SelectField::new('specialisationValues', '@lawyer.admin.field.specialisations')->setChoices(Specialisation::choices())->allowMultipleChoices()->setRequired(false)->hideOnIndex()->setHelp('@lawyer.admin.help.specialisations');
        yield TextField::new('qualification', '@lawyer.admin.field.qualification')->setRequired(false)->hideOnIndex()->setHelp('@lawyer.admin.help.qualification');
        yield TextareaField::new('dominantFieldsText', '@lawyer.admin.field.dominant_fields')->setRequired(false)->hideOnIndex()->setHelp('@lawyer.admin.help.dominant_fields');
    }
}
