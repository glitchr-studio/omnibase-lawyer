<?php

namespace Base\Lawyer\Controller\Client;

use Base\Office\Repository\Booking\AppointmentRepository;
use Base\Office\Repository\Share\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The client's space: the appointments to come, the documents the office
 * sent and those the client deposited - in the encrypted vault, never by
 * e-mail.
 */
#[IsGranted('ROLE_USER')]
class SpaceController extends AbstractController
{
    #[Route('/espace', name: 'lawyer_space', methods: ['GET'])]
    public function index(AppointmentRepository $appointments, DocumentRepository $documents): Response
    {
        $user = $this->getUser();

        return $this->render('@Lawyer/client/space/index.html.twig', [
            'upcoming' => $appointments->findForClient($user, true, 5),
            'unread' => $documents->countUnread($user),
            'documents' => \array_slice($documents->findForRecipient($user), 0, 4),
        ]);
    }
}
