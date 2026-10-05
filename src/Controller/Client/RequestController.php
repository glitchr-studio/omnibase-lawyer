<?php

namespace Base\Lawyer\Controller\Client;

use Base\Office\Controller\Client\AbstractRequestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Asking for a first appointment without an account: an office's clients
 * are invited, so someone new could not go through omnibase/office's
 * booking pages, which ask to sign in. The request becomes an appointment
 * of the office in request mode (no time yet: the office fixes it), under
 * the name, the e-mail and the phone given; the few words are kept
 * encrypted, as every reason is.
 *
 * The form, its model and what is done with the request are
 * omnibase/office's (AbstractRequestController, AppointmentRequests); the
 * route, the page and its words are this regime's.
 */
class RequestController extends AbstractRequestController
{
    #[Route('/rendez-vous/demande', name: 'lawyer_request', methods: ['GET', 'POST'], priority: 10)]
    public function request(Request $request): Response
    {
        return $this->respond($request, '@Lawyer/client/request.html.twig', 'lawyer', '@lawyer.request');
    }
}
