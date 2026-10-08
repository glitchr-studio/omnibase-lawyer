# omnibase/lawyer

The lawyers' regime on [omnibase/office](https://github.com/glitchr-studio/omnibase-office): the site of a
French law firm, within article 10 of the Règlement intérieur national (RIN).

- **Compliance checks** in the back office's "Conformité" widget, each resting on a text cited in
  [docs/rules.md](docs/rules.md): the domain name carries the lawyer's name or the firm's
  denomination (10.5), specialisations come from the certified list and the four words
  "spécialiste / spécialisé / spécialité / spécialisation" are for the holders of a certificate, with
  three dominant fields at most (10.2), the wording carries no comparison nor reference to judicial
  functions (10.2), the firm is identified with its bar and its structure of practice (10.2), the
  site is declared to the conseil de l'Ordre (10.5), the fees are explained (11.1, 11.2), the consumer
  mediator of the profession is named;
- **the memo for the Ordre** (`/admin/lawyer/declaration`): what the site says today and must be
  communicated - domain names, specialisations and dominant fields in their terms, the pages that
  present the services;
- **its pages**: the fields of practice (`/domaines`), the fees and the mandatory fee agreement
  (`/honoraires`), the mediation (`/mediation`), a first appointment request open to someone who has
  no account yet (`/rendez-vous/demande`), the client's space (`/espace`) on the office's encrypted vault;
- **professional secrecy in who reads a client's document**: the lawyers; the secretariat sees that
  it exists, not what it says;
- **paying fees online**: a hook only - the fees page links to the application's payment page when
  omnibase/marketplace is installed (`class_exists`) and `lawyer.payment` is set; fees, never
  clients' funds.

> **Compliance built into the code does not replace the Ordre's control.** The checks help a firm
> not to forget; the conseil de l'Ordre, told of the site and of its domain names, remains the only
> judge of their conformity. What could not be read in an official text is listed as "to confirm"
> in [docs/rules.md](docs/rules.md), and is not written as a rule.

```sh
composer require omnibase/lawyer        # brings omnibase/office
```

Documentation: [docs/](docs/index.md). License: MIT since 2026-10-09; earlier versions remain published under LGPL-3.0-or-later.
