<?php

namespace Base\Lawyer\Tests\Demo;

use Base\Demo\DemoAccountRegistry;
use Base\Lawyer\Demo\LawyerDemoAccounts;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Yaml\Yaml;

/**
 * The demonstration accounts of a law firm: one for each role, the firm's
 * roles through their groups, a label and a sentence for each in the
 * bundle's catalogue - and nobody above the firm's administrator.
 */
class LawyerDemoAccountsTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(DemoAccountRegistry::class)) {
            self::markTestSkipped('Requires a glitchr/omnibase with the demo environment.');
        }
    }

    /** The role hierarchy the bundle's documentation gives an application (docs/index.md). */
    private function registry(): DemoAccountRegistry
    {
        return new DemoAccountRegistry([new LawyerDemoAccounts()], [], new RoleHierarchy([
            'ROLE_LAWYER' => ['ROLE_STAFF'],
            'ROLE_SECRETARY' => ['ROLE_STAFF'],
            'ROLE_STAFF' => ['ROLE_USER'],
            'ROLE_ADMIN' => ['ROLE_STAFF'],
            'ROLE_SUPERADMIN' => ['ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'],
            'ROLE_EDITOR' => ['ROLE_SUPERADMIN'],
        ]));
    }

    public function testOneAccountForEachRoleOfTheFirm(): void
    {
        $accounts = $this->registry()->all();

        $this->assertSame(['avocate', 'avocat', 'secretariat', 'cabinet', 'client'], array_keys($accounts));
        foreach (['avocate', 'avocat'] as $lawyer) {
            $this->assertSame(LawyerDemoAccounts::LAWYERS, $accounts[$lawyer]->group);
            $this->assertSame(['ROLE_USER', 'ROLE_LAWYER'], $accounts[$lawyer]->getAllRoles());
        }
        $this->assertSame(LawyerDemoAccounts::SECRETARIES, $accounts['secretariat']->group);
        $this->assertSame(['ROLE_USER', 'ROLE_SECRETARY'], $accounts['secretariat']->getAllRoles());
        $this->assertSame(['ROLE_ADMIN'], $accounts['cabinet']->getAllRoles());
        $this->assertSame(LawyerDemoAccounts::CLIENTS, $accounts['client']->group);
        $this->assertSame(['ROLE_USER'], $accounts['client']->getAllRoles(), 'a client holds no role of the firm');
        $this->assertSame('avocate', $accounts['avocate']->getPassword(), 'the password is the identifier, as in the fixtures');
    }

    public function testNobodyAboveTheFirmsAdministrator(): void
    {
        $registry = $this->registry();
        foreach ($registry->all() as $account) {
            $this->assertFalse($registry->reachesSuperAdmin($account->getAllRoles()), $account->identifier);
        }
    }

    public function testEachHasItsLabelAndItsSentenceInTheCatalogue(): void
    {
        $catalogue = Yaml::parseFile(\dirname(__DIR__, 2).'/translations/lawyer+intl-icu.fr.yaml')['demo'];

        foreach ($this->registry()->all() as $identifier => $account) {
            $this->assertSame('@lawyer.demo.'.$identifier.'.label', $account->label);
            $this->assertSame('@lawyer.demo.'.$identifier.'.description', $account->description);
            $this->assertNotEmpty($catalogue[$identifier]['label'] ?? null, $identifier);
            $this->assertNotEmpty($catalogue[$identifier]['description'] ?? null, $identifier);
        }
    }
}
