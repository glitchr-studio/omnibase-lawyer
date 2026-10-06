<?php

namespace Base\Lawyer\Demo;

use Base\Demo\DemoAccount;
use Base\Demo\DemoAccountProviderInterface;

/**
 * The demonstration accounts of a law firm (glitchr/omnibase's `demo`
 * environment): one for each role this bundle and omnibase/office know. The
 * roles of the firm come from two groups, as in a real one - "Avocats"
 * (ROLE_LAWYER) and "Secrétariat" (ROLE_SECRETARY), both ROLE_STAFF through
 * the application's role hierarchy; "cabinet" is the firm's administrator.
 *
 * An application's fixtures take them from Base\Demo\DemoAccountFactory
 * ($accounts->account('avocate', $manager)) and attach what makes them worth
 * signing in as: a member of the team and an agenda to the lawyers, a request
 * and documents in the vault to the client. A firm without one of these
 * roles (a lawyer practising alone has no associate) leaves it out:
 * base.demo.exclude.
 *
 * Registered when the installed glitchr/omnibase has the demo environment
 * (config/services.php).
 */
final class LawyerDemoAccounts implements DemoAccountProviderInterface
{
    public const LAWYERS = 'Avocats';
    public const SECRETARIES = 'Secrétariat';
    public const CLIENTS = 'Clients';

    public function getDemoAccounts(): iterable
    {
        yield new DemoAccount('avocate', '@lawyer.demo.avocate.label', '@lawyer.demo.avocate.description', group: self::LAWYERS, groupRoles: ['ROLE_LAWYER'], position: 10);
        yield new DemoAccount('avocat', '@lawyer.demo.avocat.label', '@lawyer.demo.avocat.description', group: self::LAWYERS, groupRoles: ['ROLE_LAWYER'], position: 20);
        yield new DemoAccount('secretariat', '@lawyer.demo.secretariat.label', '@lawyer.demo.secretariat.description', group: self::SECRETARIES, groupRoles: ['ROLE_SECRETARY'], position: 30);
        yield new DemoAccount('cabinet', '@lawyer.demo.cabinet.label', '@lawyer.demo.cabinet.description', roles: ['ROLE_ADMIN'], position: 40);
        yield new DemoAccount('client', '@lawyer.demo.client.label', '@lawyer.demo.client.description', group: self::CLIENTS, position: 50);
    }
}
