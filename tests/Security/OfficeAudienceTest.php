<?php

namespace Base\Lawyer\Tests\Security;

use App\Entity\User;
use Base\Lawyer\Security\OfficeAudience;
use Base\Office\Entity\Share\Document;
use Base\Office\Share\AudienceResolverInterface as Audience;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

/** Professional secrecy within the firm: who sees and reads a client's document, beyond the client and its sender. */
final class OfficeAudienceTest extends TestCase
{
    public function testLawyersRead(): void
    {
        foreach (['ROLE_LAWYER'] as $role) {
            self::assertTrue($this->audience()->decide(Audience::READ, $this->document(), $this->user([$role])), $role);
            self::assertTrue($this->audience()->decide(Audience::VIEW, $this->document(), $this->user([$role])), $role);
            self::assertNull($this->audience()->decide(Audience::REVOKE, $this->document(), $this->user([$role])), 'only its sender withdraws a document');
        }
    }

    public function testTheSecretariatSeesTheTitleNotTheContent(): void
    {
        $reception = $this->user(['ROLE_SECRETARY']);

        self::assertTrue($this->audience()->decide(Audience::VIEW, $this->document(), $reception));
        self::assertNull($this->audience()->decide(Audience::READ, $this->document(), $reception));
        self::assertTrue($this->audience()->decide(Audience::READ, $this->document(confidential: false), $reception));
    }

    public function testNobodyOutsideTheFirm(): void
    {
        foreach ([[], ['ROLE_USER'], ['ROLE_SOMETHING_ELSE']] as $roles) {
            self::assertNull($this->audience()->decide(Audience::READ, $this->document(confidential: false), $this->user($roles)));
            self::assertNull($this->audience()->decide(Audience::VIEW, $this->document(), $this->user($roles)));
        }
    }

    private function audience(): OfficeAudience
    {
        return new OfficeAudience(new RoleHierarchy(['ROLE_LAWYER' => ['ROLE_STAFF'], 'ROLE_SECRETARY' => ['ROLE_STAFF'], 'ROLE_STAFF' => ['ROLE_USER']]));
    }

    private function document(bool $confidential = true): Document
    {
        return (new Document($this->createStub(User::class)))->setConfidential($confidential);
    }

    /** @param list<string> $roles */
    private function user(array $roles): User
    {
        $user = $this->createStub(User::class);
        $user->method('getRoles')->willReturn($roles);

        return $user;
    }
}
