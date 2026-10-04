<?php

namespace Base\Lawyer\Form;

use Base\Form\Type\PrivacyType;
use Base\Lawyer\Form\Model\AppointmentRequest;
use Base\Office\Entity\Booking\AppointmentType;
use Base\Office\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The first appointment request, open to someone who has no account: what
 * it is about (a type in request mode), with whom if they know, how to
 * reach them, a few words - and omnibase's data-protection notice with its
 * box to tick.
 */
class AppointmentRequestType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AppointmentRequest::class,
            'types' => [],
            'members' => [],
            'privacy_parameters' => [],
        ]);
        $resolver->setAllowedTypes('types', 'array');
        $resolver->setAllowedTypes('members', 'array');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => '@lawyer.request.type',
                'choices' => $options['types'],
                'choice_label' => static fn (AppointmentType $type): string => $type->getName(),
                'choice_value' => static fn (?AppointmentType $type): string => (string) $type?->getId(),
                'choice_translation_domain' => false,
                'placeholder' => 1 === \count($options['types']) ? false : '@lawyer.request.choose',
            ])
            ->add('member', ChoiceType::class, [
                'label' => '@lawyer.request.member',
                'required' => false,
                'choices' => $options['members'],
                'choice_label' => static fn (Member $member): string => $member->getDisplayName(),
                'choice_value' => static fn (?Member $member): string => (string) $member?->getId(),
                'choice_translation_domain' => false,
                'placeholder' => '@lawyer.request.no_preference',
            ])
            ->add('name', TextType::class, ['label' => '@lawyer.request.name', 'attr' => ['autocomplete' => 'name']])
            ->add('email', EmailType::class, ['label' => '@lawyer.request.email', 'attr' => ['autocomplete' => 'email']])
            ->add('phone', TelType::class, ['label' => '@lawyer.request.phone', 'attr' => ['autocomplete' => 'tel']])
            ->add('message', TextareaType::class, ['label' => '@lawyer.request.message', 'required' => false, 'help' => '@lawyer.request.message_help', 'attr' => ['rows' => 4, 'maxlength' => 1000]])
            // Off-screen for people (and for screen readers), filled by robots.
            ->add('website', TextType::class, ['required' => false, 'label' => false,
                'row_attr' => ['class' => 'base-trap', 'aria-hidden' => 'true', 'style' => 'position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden'],
                'attr' => ['tabindex' => '-1', 'autocomplete' => 'off']])
            ->add('privacy', PrivacyType::class, [
                'notice' => '@lawyer.request.privacy',
                'notice_parameters' => $options['privacy_parameters'],
                'consent' => true,
            ]);
    }
}
