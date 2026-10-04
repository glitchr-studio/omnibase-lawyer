---
title: Rules and sources
order: 2
---

# Rules and sources

Each rule the bundle codes, the text it rests on, where that text was read, and the class and
test that carry it. Read on 2026-10-05.

> Compliance built into the code does not replace the Ordre's control: the conseil de l'Ordre is
> the only judge of a site's conformity.

## The texts

| Text | Read at |
|---|---|
| **RIN** - Règlement intérieur national de la profession d'avocat, Conseil national des barreaux, consolidated version of 7 July 2026 (articles 2, 6, 10, 11) | <https://www.cnb.avocat.fr/fr/reglement-interieur-national-de-la-profession-davocat-rin> (the PDF: <https://cnb.avocat.fr/medias/file/cnbrinmaj-08072026-6a4e79014f6b41.49207715.pdf>) |
| The list of the mentions of specialisation (order of the garde des Sceaux of 28 December 2011), 28 mentions, two at most per lawyer | as the Conseil national des barreaux publishes it: <https://www.cnb.avocat.fr/sites/default/files/documents/cnb_plaquette_specification_2022_pap.pdf> |
| The consumer mediator of the lawyers' profession | its own site: <https://mediateur-consommation-avocat.fr> |

## Coded rules

| # | Rule | Text | Class | Test |
|---|---|---|---|---|
| 1 | The domain name carries the lawyer's name or the firm's denomination, in full or abbreviated, before or after the word "avocat" | RIN art. 10.5: « Le nom de domaine doit comporter le nom de l'avocat ou la dénomination du cabinet en totalité ou en abrégé, qui peut être suivi ou précédé du mot « avocat ». » | `Guard\DomainName`, `Service\Domains`, `Compliance\DomainNameCheck` | `DomainNameTest`, `ChecksTest` |
| 2 | A generic domain name is forbidden | RIN art. 10.5: « L'utilisation de noms de domaine évoquant de façon générique le titre d'avocat ou un titre pouvant prêter à confusion, un domaine du droit ou une activité relevant de celles de l'avocat, est interdite. » | the same (`GENERIC`: missing; `MIXED`: a warning) | `DomainNameTest` |
| 3 | The site and its domain names are declared to the conseil de l'Ordre | RIN art. 10.5: « L'avocat qui ouvre ou modifie substantiellement un site Internet doit en informer le conseil de l'Ordre sans délai et lui communiquer les noms de domaine qui permettent d'y accéder. » | `Compliance\OrderDeclarationCheck`, the memo | `ChecksTest` |
| 4 | What is said of specialisations and dominant fields is sent to the Ordre in its terms; any publicity too | RIN art. 10.2 (« doit transmettre les termes de cette communication sans délai au conseil de l'Ordre ») ; art. 10.3 (« Toute publicité doit être communiquée sans délai au conseil de l'Ordre. ») | `Controller\Admin\DeclarationController` (the memo lists them) | the application's test |
| 5 | Only certified specialisations are shown | RIN art. 10.2: the lawyer may mention « sa ou ses spécialisations [...] régulièrement obtenues et non invalidées » | `Enum\Specialisation` (the 28 mentions), `Entity\Attorney::setSpecialisationValues()` | `AttorneyTest` |
| 6 | The four words are for the holders of a certificate | RIN art. 10.2: « Seul l'avocat titulaire d'un ou de plusieurs certificats de spécialisation [...] peut utiliser pour sa communication [...] les mots « spécialiste », « spécialisé », « spécialité » ou « spécialisation » » | `Guard\SpecialistWords`, `Compliance\SpecialisationCheck` | `SpecialistWordsTest`, `ChecksTest` |
| 7 | Three dominant fields at most | RIN art. 10.2: « L'information relative aux domaines d'activités dominantes, dont le nombre revendiqué ne peut être supérieur à trois » | `Attorney::MAX_DOMINANT_FIELDS` (constraint), `SpecialisationCheck` | `ChecksTest` |
| 8 | Two mentions of specialisation at most | Conseil national des barreaux: « Un avocat peut obtenir et faire usage de deux mentions de spécialisation au maximum » | `Specialisation::MAX` (constraint), `SpecialisationCheck` | `ChecksTest` |
| 9 | No comparison, no disparagement, no reference to judicial functions | RIN art. 10.2: « Sont prohibées : toute publicité mensongère ou trompeuse ; toute mention comparative ou dénigrante ; [...] toute référence à des fonctions juridictionnelles. » | `Guard\Wording`, `Compliance\WordingCheck` | `WordingTest`, `ChecksTest` |
| 10 | The firm is identified, located, reachable, with its bar and its structure of practice | RIN art. 10.2: the lawyer must « faire état de sa qualité et permettre [...] de l'identifier, de le localiser, de le joindre, de connaître le barreau auquel il est inscrit, la structure d'exercice à laquelle il appartient et, le cas échéant, le réseau dont il est membre » | `Compliance\IdentificationCheck` | `ChecksTest` |
| 11 | The client is told how fees are set | RIN art. 11.1: « L'avocat informe son client, dès sa saisine, des modalités de détermination des honoraires et l'informe régulièrement de l'évolution de leur montant. » | `Compliance\FeesInformationCheck`, `/honoraires` | `ChecksTest` |
| 12 | A written fee agreement, announced on the fees page | RIN art. 11.2: « Sauf en cas d'urgence ou de force majeure ou lorsqu'il intervient au titre de l'aide juridictionnelle totale [...], l'avocat conclut par écrit avec son client une convention d'honoraires » | the fees page's text | the application's test |
| 13 | No quota litis | RIN art. 11.3: « Il est interdit à l'avocat de fixer ses honoraires par un pacte de quota litis. » | the fees page's text | the application's test |
| 14 | Paying online is for fees only; clients' funds go to the CARPA | RIN art. 11.5 (« Les honoraires sont payés [...] notamment en espèces, par chèque, par virement, par billet à ordre et par carte bancaire ») ; art. 6.2 (« L'avocat qui manie les fonds, effets ou valeurs de manière accessoire à une opération juridique ou judiciaire doit les déposer sans délai à la CARPA ») | `Payment\FeePayment`, `Compliance\PaymentScopeCheck` | `FeePaymentTest` |
| 15 | The consumer mediator of the profession is named with its coordinates | the mediator's site (address, e-mail, form) | `Compliance\MediatorCheck` (a warning), `/mediation`, `lawyer.mediator` | `ChecksTest` |
| 16 | Within the firm the lawyers read a client's document; the secretariat sees a title | the firm's choice, under RIN art. 2.1: « Le secret professionnel de l'avocat est d'ordre public. Il est général, absolu et illimité dans le temps. » | `Security\OfficeAudience` | `OfficeAudienceTest` |

`Guard\Wording`, `Guard\SpecialistWords` and `Guard\DomainName` read words: a help to whoever
writes, shown in the back office. What they do not find is not thereby allowed.

## What the application must keep (no check can see a template)

- RIN art. 10.5: « Le site de l'avocat ne peut comporter aucun encart ou bannière publicitaire,
  autres que ceux de la profession, pour quelque produit ou service que ce soit. »
- RIN art. 10.5: no hyperlink to a site whose content is contrary to the profession's essential
  principles; the lawyer visits the linked sites regularly. The memo recalls both.
- RIN art. 10.6: a denomination evoking generically the title, a field of law, a specialisation or
  an activity of the profession is forbidden - the same reading as the domain name's applies to the
  firm's name, which the bundle does not check.

## To confirm (not coded as rules, or coded with this reserve)

- The **current list of the mentions of specialisation**: read in the CNB's 2022 leaflet (28
  mentions); the order of 28 December 2011 as amended could not be read on Légifrance (it refuses
  automated readers). To be compared before a real firm relies on it.
- The **article of the code de la consommation** that has a professional give the mediator's
  coordinates (art. L. 616-1, as recalled from memory) was not read: the mediator and its coordinates
  are confirmed, the legal basis of the obligation is not - hence a warning, not a missing mention.
- **Loi n° 71-1130 du 31 décembre 1971, art. 10** (fees, the mandatory agreement): cited by the RIN
  in the heading of its article 11, not read itself.
- The **contest of fees before the bâtonnier** (the fees and mediation pages say it): from RIN art.
  11.5 (« il devra saisir son bâtonnier aux fins de taxation ») and the decree of 27 November 1991
  cited in the heading of article 11, not read itself.
- The name of the mediator in office changes (a term of a few years): the configuration names the
  institution, not the person.
- Each **bar's own rules** (its règlement intérieur) may add to the RIN: not known to the bundle.
