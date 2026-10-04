<?php

namespace Base\Lawyer\Controller\Client;

use Base\Lawyer\Form\AppointmentRequestType;
use Base\Lawyer\Form\Model\AppointmentRequest;
use Base\Office\Booking\Booker;
use Base\Office\Booking\BookingPolicy;
use Base\Office\Entity\Booking\AppointmentType;
use Base\Office\Entity\Member;
use Base\Office\Exception\BookingException;
use Base\Office\Exception\KeyMissingException;
use Base\Office\Repository\Booking\AppointmentTypeRepository;
use Base\Office\Repository\MemberRepository;
use Base\Office\Repository\OfficeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Asking for a first appointment without an account: an office's clients
 * are invited, so someone new could not go through omnibase/office's
 * booking pages, which ask to sign in. The request becomes an appointment
 * of the office in request mode (no time yet: the office fixes it), under
 * the name, the e-mail and the phone given; the few words are kept
 * encrypted, as every reason is.
 */
class RequestController extends AbstractController
{
    #[Route('/rendez-vous/demande', name: 'lawyer_request', methods: ['GET', 'POST'], priority: 10)]
    public function request(Request $request, MemberRepository $members, AppointmentTypeRepository $types, BookingPolicy $policy, Booker $booker, OfficeRepository $offices, TranslatorInterface $translator): Response
    {
        // What can be asked for, and of whom: the types in request mode open to everyone.
        $offered = $bookable = [];
        foreach ($members->findBookable() as $member) {
            foreach ($types->findForMember($member) as $type) {
                if ($type->isRequest() && $policy->canBook($type, $member, null)) {
                    $offered[$type->getId()] = $type;
                    $bookable[$member->getId()] = $member;
                }
            }
        }
        usort($offered, static fn (AppointmentType $a, AppointmentType $b) => $a->getPosition() <=> $b->getPosition());

        $user = $this->getUser();
        $model = new AppointmentRequest();
        if (null !== $user) {
            $model->name = (string) $user;
            $model->email = method_exists($user, 'getEmail') ? $user->getEmail() : null;
        }
        $wanted = (string) $request->query->get('avec');
        foreach ($bookable as $member) {
            if ('' !== $wanted && $member->getSlug() === $wanted) {
                $model->member = $member;
            }
        }
        if (1 === \count($offered)) {
            $model->type = $offered[0];
        }

        $form = $this->createForm(AppointmentRequestType::class, $model, [
            'types' => $offered,
            'members' => array_values($bookable),
            'privacy_parameters' => ['months' => $this->getParameter('office.contact.retention_months')],
        ]);
        $form->handleRequest($request);
        $sent = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($model->isRobot()) {
                $sent = true;
            } else {
                $member = $this->memberFor($model, $policy);
                try {
                    if (null === $member) {
                        throw new BookingException('booking.error.not_bookable');
                    }
                    $booker->request($model->type, $member, $user instanceof \App\Entity\User ? $user : null, $model->message, null, ['name' => $model->name, 'email' => mb_strtolower((string) $model->email), 'phone' => $model->phone]);
                    $sent = true;
                } catch (BookingException $e) {
                    $form->addError(new FormError($translator->trans($e->getKey(), $e->getParameters(), 'office')));
                } catch (KeyMissingException) {
                    $form->addError(new FormError($translator->trans('request.unavailable', [], 'lawyer')));
                }
            }
        }

        return $this->render('@Lawyer/client/request.html.twig', [
            'form' => $form,
            'sent' => $sent,
            'available' => [] !== $offered,
            'office' => $offices->findMain(),
        ], new Response(null, $form->isSubmitted() && !$sent ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    /** The member asked for when they offer that type, else the first who does. */
    private function memberFor(AppointmentRequest $model, BookingPolicy $policy): ?Member
    {
        if (null === $model->type) {
            return null;
        }
        if (null !== $model->member && $policy->canBook($model->type, $model->member, null)) {
            return $model->member;
        }
        foreach ($model->type->getMembers() as $member) {
            if ($policy->canBook($model->type, $member, null)) {
                return $member;
            }
        }

        return null;
    }
}
