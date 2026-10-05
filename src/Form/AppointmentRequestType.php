<?php

namespace Base\Lawyer\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * omnibase/office's form for a first appointment asked without an account,
 * with this regime's labels (@lawyer.request.*). Kept for whoever builds the
 * form by this name; the regime's own page gives the prefix itself.
 */
class AppointmentRequestType extends \Base\Office\Form\AppointmentRequestType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('label_prefix', '@lawyer.request');
    }
}
