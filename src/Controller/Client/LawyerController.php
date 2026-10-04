<?php

namespace Base\Lawyer\Controller\Client;

use Base\Lawyer\Payment\FeePayment;
use Base\Lawyer\Repository\AreaRepository;
use Base\Lawyer\Service\Record;
use Base\Office\Repository\Booking\AppointmentTypeRepository;
use Base\Office\Repository\MemberRepository;
use Base\Office\Repository\OfficeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The lawyers' regime's public pages: the fields of practice, the fees -
 * how they are set, the fee agreement, how they are paid - and the consumer
 * mediator of the profession.
 */
class LawyerController extends AbstractController
{
    public function __construct(private readonly OfficeRepository $offices, private readonly Record $record)
    {
    }

    #[Route('/domaines', name: 'lawyer_areas', methods: ['GET'])]
    public function areas(AreaRepository $areas): Response
    {
        return $this->render('@Lawyer/client/areas.html.twig', ['areas' => $areas->findActive(), 'office' => $this->offices->findMain()]);
    }

    #[Route('/domaines/{slug}', name: 'lawyer_area', methods: ['GET'], requirements: ['slug' => '[a-z0-9\-]+'])]
    public function area(string $slug, AreaRepository $areas): Response
    {
        $area = $areas->findOneActiveBySlug($slug) ?? throw $this->createNotFoundException();

        return $this->render('@Lawyer/client/area.html.twig', ['area' => $area, 'areas' => $areas->findActive(), 'office' => $this->offices->findMain()]);
    }

    #[Route('/honoraires', name: 'lawyer_fees', methods: ['GET'])]
    public function fees(FeePayment $payment, MemberRepository $members, AppointmentTypeRepository $types): Response
    {
        // What a first meeting costs, as the appointment types print it.
        $priced = [];
        foreach ($members->findBookable() as $member) {
            foreach ($types->findForMember($member) as $type) {
                if (null !== $type->getPrice()) {
                    $priced[$type->getId()] = $type;
                }
            }
        }

        return $this->render('@Lawyer/client/fees.html.twig', [
            'office' => $this->offices->findMain(),
            'record' => $this->record,
            'payment' => $payment,
            'priced' => array_values($priced),
        ]);
    }

    #[Route('/mediation', name: 'lawyer_mediation', methods: ['GET'])]
    public function mediation(): Response
    {
        return $this->render('@Lawyer/client/mediation.html.twig', [
            'office' => $this->offices->findMain(),
            'record' => $this->record,
            'mediator' => $this->getParameter('lawyer.mediator'),
        ]);
    }
}
