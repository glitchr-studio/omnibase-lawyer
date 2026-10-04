<?php

namespace Base\Lawyer\Service;

use Base\Lawyer\Guard\DomainName;
use Base\Lawyer\Repository\AttorneyRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The site's domain names and how each reads against RIN art. 10.5: those
 * of the configuration (lawyer.domains), else the host of the router's
 * default address; the names they may carry are the firm's denomination and
 * the lawyers'.
 */
class Domains
{
    /** @param list<string> $domains */
    public function __construct(
        private readonly Record $record,
        private readonly AttorneyRepository $attorneys,
        #[Autowire('%lawyer.domains%')] private readonly array $domains = [],
        #[Autowire('%lawyer.default_uri%')] private readonly ?string $defaultUri = null,
    ) {
    }

    /** @return list<string> */
    public function all(): array
    {
        if ([] !== $this->domains) {
            return array_values(array_unique(array_map('mb_strtolower', $this->domains)));
        }
        $host = parse_url((string) $this->defaultUri, \PHP_URL_HOST);

        return \is_string($host) && '' !== $host ? [mb_strtolower($host)] : [];
    }

    /** @return list<string> the firm's denomination and its lawyers' names */
    public function names(): array
    {
        $names = array_filter([$this->record->getFirm()]);
        foreach ($this->attorneys->findVisible() as $attorney) {
            $names[] = (string) $attorney->getMember()?->getDisplayName();
        }

        return array_values(array_filter($names));
    }

    /** @return array<string, string> domain => DomainName verdict */
    public function verdicts(): array
    {
        $names = $this->names();
        $verdicts = [];
        foreach ($this->all() as $domain) {
            $verdicts[$domain] = DomainName::assess($domain, $names);
        }

        return $verdicts;
    }
}
