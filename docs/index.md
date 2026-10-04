---
title: omnibase/lawyer
order: 1
---

# omnibase/lawyer

The lawyers' regime on omnibase/office. What a regime brings: its mandatory mentions and its
safeguards, coded and tested; its own pages; its settings. The rules and their sources are in
[Rules and sources](rules.md).

> Compliance built into the code does not replace the Ordre's control.

## Installation

```sh
composer require omnibase/lawyer          # brings omnibase/office
```

```php
// config/bundles.php
Base\Office\OfficeBundle::class => ['all' => true],
Base\Lawyer\LawyerBundle::class => ['all' => true],
```

```yaml
# config/routes.yaml - after omnibase/office's
lawyer_controller:
    resource: "@LawyerBundle/src/Controller/Client"
    type: attribute
lawyer_admin_controller:
    resource: "@LawyerBundle/src/Controller/Admin"
    type: attribute
```

```yaml
# config/packages/security.yaml
security:
    role_hierarchy:
        ROLE_LAWYER:    [ROLE_STAFF]
        ROLE_SECRETARY: [ROLE_STAFF]
        ROLE_STAFF:     [ROLE_USER]
        ROLE_ADMIN:     [ROLE_STAFF]
```

The roles are given by omnibase's groups (`Base\Entity\User\Group`): a group "Avocats" whose roles
are `[ROLE_LAWYER]`, a group "Secrétariat" with `[ROLE_SECRETARY]`.

Then a migration: three tables, `lawyer_attorney`, `lawyer_area`, `lawyer_area_member`.

In the layout, after the office's stylesheet:

```twig
<link rel="stylesheet" href="{{ asset('bundles/lawyer/css/lawyer.css') }}">
```

## Configuration

```yaml
# config/packages/lawyer.yaml - every key has a default
lawyer:
    roles: { lawyer: ROLE_LAWYER, secretary: ROLE_SECRETARY, staff: ROLE_STAFF }
    domains: ['dupont-avocat.fr']          # checked against RIN art. 10.5; none: the host of DEFAULT_URI
    mediator:                              # printed on /mediation and in the legal notice
        name: 'Médiateur de la consommation de la profession d’avocat'
        address: '180 boulevard Haussmann, 75008 Paris'
        email: 'mediateur-conso@mediateur-consommation-avocat.fr'
        url: 'https://mediateur-consommation-avocat.fr'
    rules_url: 'https://www.cnb.avocat.fr/fr/reglement-interieur-national-de-la-profession-davocat-rin'
    payment:
        enabled: false                     # see "Paying fees online"
        route: ~
    wording: []                            # expressions added to the built-in list
```

The bundle sets three of omnibase/office's options (`prepend`): the warning above the contact form
(`@lawyer.contact.notice`: nothing confidential here, and no relation created), the menu of the
client's space, and where the booking e-mails send the client (`lawyer_space`).

## Settings (back office, Réglages)

What only the firm knows, kept by omnibase's settings and read by `Base\Lawyer\Service\Record`:

| Setting | |
|---|---|
| `lawyer.firm.name` | the firm's denomination - what the domain name is read against |
| `lawyer.firm.structure` | the structure of practice (individual, SELARL, AARPI...) |
| `lawyer.firm.bar` | the bar it is registered with |
| `lawyer.firm.network` | the network it belongs to, if any |
| `lawyer.order.declared_at` | the day the site was declared to the conseil de l'Ordre (`YYYY-MM-DD`) |
| `lawyer.fees.terms` | how the fees are set (hourly rate, flat fees...) |
| `lawyer.fees.first_meeting` | the first meeting: how long, what it costs |
| `lawyer.fees.legal_aid` | legal aid: accepted or not |

## The lawyers

`Base\Lawyer\Entity\Attorney`, one per member of the team who is a lawyer (as omnibase/health's
`Practitioner` is to a `Member`): `bar`, `swornIn`, `specialisations`, `qualification`,
`dominantFields`.

- A specialisation is a case of `Base\Lawyer\Enum\Specialisation` - the 28 mentions of the official
  list. `setSpecialisationValues()` drops anything else: a specialisation is a certificate, not a
  wish. Two at most (`Specialisation::MAX`, a validation constraint).
- Dominant fields: free words, three at most (`Attorney::MAX_DOMINANT_FIELDS`, a validation
  constraint, RIN art. 10.2).

```twig
{% include '@Lawyer/client/_attorney.html.twig' with {attorney: lawyer_attorney(member)} only %}
```

## The domain name

```php
DomainName::assess('mareval-avocat.fr', ['Cabinet Maréval', 'Me Hortense Maréval']);   // DomainName::OK
DomainName::assess('avocat-divorce.fr', $names);      // GENERIC: no name, a field of law - forbidden
DomainName::assess('mareval-divorce.fr', $names);     // MIXED: the name, and a field of law besides
DomainName::assess('lexium.fr', $names);              // UNNAMED: no name the site knows
DomainName::assess('localhost', $names);              // LOCAL: nothing to assess
```

The names are the firm's denomination and its lawyers' (`Base\Lawyer\Service\Domains`); the initials
of a denomination count as its abbreviation ("dma" for Dupont Martin & Associés). A reading aid: the
conseil de l'Ordre decides.

## Pages

| Route | Path | |
|---|---|---|
| `lawyer_areas`, `lawyer_area` | `/domaines`, `/domaines/{slug}` | the fields of practice (`Area`: summary, text, matters, who follows it) |
| `lawyer_fees` | `/honoraires` | the fee agreement, how fees are set, quota litis forbidden, the first meeting, legal aid, how to pay, disagreement |
| `lawyer_mediation` | `/mediation` | the firm first, then the consumer mediator of the profession and its coordinates; the bâtonnier |
| `lawyer_request` | `/rendez-vous/demande` | a first appointment asked without an account |
| `lawyer_space` | `/espace` | the client's space: appointments, documents |
| `lawyer_admin_declaration` | `/admin/lawyer/declaration` | the memo for the Ordre (`ROLE_ADMIN`) |

Twig: `lawyer_record()`, `lawyer_attorney(member)`, `lawyer_areas(member)`, `lawyer_mediator()`,
`lawyer_payment()`, `lawyer_rules_url()`.

### A first appointment without an account

omnibase/office's booking pages ask to sign in, and a firm's clients are invited, not registered.
`/rendez-vous/demande` is a Symfony form (`AppointmentRequestType`, with omnibase's `PrivacyType` and
its box) that calls `Booker::request()` with no account: an appointment with status `requested`, the
words encrypted like every reason. Its warning asks for no confidential detail and not to name the
other party: the firm checks for a conflict of interest first.

## Paying fees online

The bundle takes no payment. `Base\Lawyer\Payment\FeePayment::isAvailable()` is true when
`Base\Marketplace\MarketplaceBundle` exists (`class_exists`), `lawyer.payment.enabled` is on and
`lawyer.payment.route` names the application's payment page; the fees page then shows "Régler une
note d'honoraires" with what it may take: fees only (RIN art. 11.5), clients' funds going to the
CARPA (art. 6.2). Writing that payment page - a fee note paid through omnibase/marketplace and
glitchr/omnitrade - is the application's.

## Who reads a document

`Base\Lawyer\Security\OfficeAudience`, beyond the client and whoever sent the document:

| | sees it exists | reads it |
|---|---|---|
| a lawyer (`ROLE_LAWYER`) | yes | yes |
| the secretariat, the rest of the staff (`ROLE_STAFF`) | yes | only what is not marked confidential |
| anyone else, the site's administrators included | no | no |

## Back office (with omnibase/admin)

The CRUD of the lawyers and of the fields of practice, the settings section, the memo for the
Ordre, and eight checks in omnibase/office's `office_compliance` widget.

## More

[Rules and sources](rules.md)
