<?php

namespace Base\Lawyer\Security;

use Base\Office\Entity\Share\Document;
use Base\Office\Share\AudienceResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Professional secrecy within the firm, as the tool applies it to a
 * client's documents, beyond the client and whoever sent the document (the
 * voter's own business):
 *
 * - the lawyers see and read it;
 * - the secretariat and the rest of the staff see that it exists and what
 *   it is called, and read only what is not marked confidential;
 * - nobody else: neither another client, nor the site's administrators as
 *   such.
 *
 * Only its sender withdraws a document.
 */
final class OfficeAudience implements AudienceResolverInterface
{
    public function __construct(
        private readonly RoleHierarchyInterface $roles,
        #[Autowire('%lawyer.roles.lawyer%')] private readonly string $lawyerRole = 'ROLE_LAWYER',
        #[Autowire('%lawyer.roles.staff%')] private readonly string $staffRole = 'ROLE_STAFF',
    ) {
    }

    public function decide(string $attribute, Document $document, UserInterface $user): ?bool
    {
        if (self::REVOKE === $attribute || null === $document->getRecipient()) {
            return null;
        }
        $held = $this->roles->getReachableRoleNames($user->getRoles());
        if (!\in_array($this->staffRole, $held, true)) {
            return null;
        }
        if (\in_array($this->lawyerRole, $held, true)) {
            return true;
        }

        return self::VIEW === $attribute || !$document->isConfidential() ? true : null;
    }
}
