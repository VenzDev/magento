# Adobe Commerce Developer Professional — analiza luk względem tego repo

Stan na: 2026-09-16 (zaktualizowano po Etapie 11 — Cron). Zweryfikowano
oficjalny blueprint egzaminu (AD0-E724,
`certification.adobe.com`) przez wyszukiwanie w sieci — patrz źródła na końcu.
Ten dokument **nie jest częścią planu nauki** (`plan-nauki-magento.md`), tylko
oceną: w jakim stopniu ukończenie tego planu przygotowuje pod konkretny
egzamin certyfikacyjny, i co trzeba dodać osobno.

## Fakt wyjściowy, który zmienia wszystko

To repo to **Magento Open Source (Community Edition) 2.4.9**
(`magento/product-community-edition` w `src/composer.json`), uruchomione na
Docker Compose (Mark Shust `docker-magento`), **nie** Adobe Commerce ani
Adobe Commerce Cloud. To oznacza, że spora część materiału egzaminacyjnego
jest tu **strukturalnie niedostępna** — nie da się jej przećwiczyć w tym
środowisku, niezależnie od tego, ile etapów planu nauki ukończysz. To nie
jest luka do "dopisania zadania", tylko luka wymagająca innego środowiska
(trial Adobe Commerce Cloud albo lokalny Commerce build).

## Struktura egzaminu (oficjalny blueprint, exam ID AD0-E724)

| Sekcja | Waga | Temat |
|---|---|---|
| 1. Architecture | **52%** | struktura modułów, CLI, cron, indeksowanie, lokalizacja, plugin/preference/observer, URL rewrites, cache, store/website/store view, architektura panelu admina, atrybuty i attribute sety |
| 2. Customizations | **36%** | operacje na katalogu, checkout i sprzedaż, manipulacja typami encji, przepływ danych do/z usług Adobe SaaS, API |
| 3. Cloud | **12%** | architektura Adobe Commerce Cloud, setup/konfiguracja Cloud, narzędzie CLI Cloud |

Próg zaliczenia: 39/50, czas: 1h40min, wymagane min. 6–12 miesięcy praktyki
z Adobe Commerce.

---

## Sekcja 1 — Architecture (52% egzaminu)

To sekcja, którą **ten plan nauki pokrywa najlepiej** — bo pokrywa się w
większości z "czystym" Magento, niezależnym od edycji.

| Temat blueprintu | Pokrycie w repo | Komentarz |
|---|---|---|
| Struktura modułu, `registration.php`, `module.xml` | ✅ Etap 1 | solidnie |
| CLI Magento/`bin/magento` | ✅ Etap 0, ciągle używane | solidnie |
| **Cron** | ✅ Etap 11 | `etc/crontab.xml` (job w grupie `default`), konfigurowalność przez `system.xml`/`config.xml` (scope store/website), weryfikacja przez `cron_schedule` — zrobione i przetestowane empirycznie (złapany i naprawiony realny bug: `retention_days=0` kasowało całą tabelę). Nieprzećwiczone: własna grupa cron (`etc/cron_groups.xml`) i `lock provider` — zostawione jako stretch goal w Etapie 11 |
| Indeksery (`update on save`/`schedule`, custom indexer) | ✅ Etap 4 | solidnie |
| **Lokalizacja / i18n** | ✅ Etap 12 | `__()` w PHP/szablonach, `i18n/<locale>.csv`, `i18n:collect-phrases`, `TimezoneInterface` do formatowania dat per locale, przełączanie `general/locale/code` — pełny cykl zweryfikowany empirycznie w `Training_HelloWorld` (`pl_PL` → tłumaczenie + polski format daty, `en_US` → fallback). Nieprzećwiczone: `general/locale/code` na poziomie store view (nie tylko `default` scope) i "Translate Inline" (stretch goal w Etapie 12) |
| Plugin / preference / observer | ✅ Etap 2 | solidnie, w tym `di.xml` |
| **URL rewrites** | ❌ brak | **luka** — brak `UrlRewrite` (entity `custom`), `url_rewrite` table, generowania rewrite'ów dla produktu/kategorii, `NoRouteHandler` |
| Cache (block cache, `cache:clean` vs `cache:flush`) | ⚠️ Etap 4, tylko cache blokowy | **luka częściowa** — brak Full Page Cache (Varnish/wbudowany FPC), brak `CacheableInterface`, tagów cache, `X-Magento-Cache-Debug` |
| **Stores / websites / store views** | ❌ brak | **luka** — cały plan działa na jednym store view; brak zadania o scope resolution (`ScopeInterface`, website-level config, per-store-view różne ceny/atrybuty), przełączaniu store code w URL |
| Architektura panelu admina (ACL, menu, UI Components) | ✅ Etap 5 | solidnie |
| Atrybuty i attribute sety | ⚠️ Etap 3, tylko product attribute select | **luka częściowa** — brak tworzenia/klonowania attribute set przez CLI/UI, brak atrybutów EAV na innych encjach (customer, category) |

**Werdykt sekcji 1:** ~60% realnie przećwiczone (po Etapach 11–12 doszły
cron i i18n), reszta (URL rewrites, FPC, multi-store) to konkretne,
dopisywalne zadania — nie wymagają Adobe Commerce, da się je zrobić w tym
repo.

## Sekcja 2 — Customizations (36% egzaminu)

| Temat blueprintu | Pokrycie w repo | Komentarz |
|---|---|---|
| Operacje na katalogu | ⚠️ Etap 3 — tylko prosty atrybut + filtr | **luka** — brak katalogowych price rules, brak configurable/bundle/grouped product (tylko simple), brak zarządzania kategoriami programowo poza tym co jest w CLAUDE.md notatce |
| **Checkout i sprzedaż** | ❌ brak całkowicie | **duża luka** — brak jakiegokolwiek zadania o `Quote`, `Order`, `Magento_Checkout` layout XML/JS (knockout components w checkout), cart price rules, shipping methods, payment methods, `SalesRule`, observery na `sales_order_place_after` itp. |
| Manipulacja typami encji | ⚠️ Etap 2/3 — custom entity (declarative schema) + product attribute | **luka częściowa** — brak tworzenia nowego **EAV entity type** (nie tylko atrybutu na istniejącym), brak custom entity z UI grid *i* EAV jednocześnie |
| **Data flow do/z usług Adobe SaaS** | ❌ niemożliwe w tym środowisku | **luka strukturalna** — Catalog Service, Live Search, Product Recommendations, Data Space Connector to usługi **Commerce/Cloud-only** (SaaS integracje przez `Magento_ServicesId`, `Magento_DataServicesGraphQl` itp.), CE (Open Source) ich nie ma |
| API (REST/GraphQL) | ✅ Etap 6, ale wąsko | **luka częściowa** — brak SOAP, brak async/bulk API (`etc/webapi_async.xml`), GraphQL tylko prosty resolver — brak mutation, brak cart/checkout GraphQL, brak custom resolvera z DI dla istniejącego typu (np. rozszerzenie `ProductInterface` w schema) |

**Werdykt sekcji 2:** to największa merytoryczna luka. Checkout/sales to
~1/3 tej sekcji i nie ma tu ani jednego zadania. Data Space/SaaS jest
niemożliwe do przećwiczenia bez Adobe Commerce (Cloud lub on-prem Commerce
license).

## Sekcja 3 — Cloud (12% egzaminu)

| Temat blueprintu | Pokrycie w repo | Komentarz |
|---|---|---|
| Architektura Adobe Commerce Cloud (Fastly CDN, GlusterFS, Galera, Cloud services) | ❌ brak | to repo to Docker Compose, nie Cloud — architektura jest inna na poziomie infrastruktury |
| Setup/konfiguracja Cloud (`.magento.app.yaml`, `.magento.env.yaml`, `.magento/routes.yaml`, `.magento/services.yaml`) | ❌ brak żadnego z tych plików w repo | **luka strukturalna** — potrzebny osobny projekt Cloud (trial) albo dokładna lektura configów bez uruchamiania |
| Cloud CLI (`magento-cloud` / `ece-tools`) | ❌ brak | wymaga konta Adobe Commerce Cloud trial |

**Werdykt sekcji 3:** cała sekcja (12% egzaminu) jest poza zasięgiem
obecnego środowiska. Nawet ukończenie Etapu 10 (CI/CD z GitHub Actions,
jeszcze niezrobiony) tego nie zastąpi — to inny stack (własny CI, nie
Cloud pipeline z `.magento.app.yaml`/`ece-tools`).

---

## Braki specyficzne dla Adobe Commerce (poza 3 sekcjami blueprintu, ale realne dla wersji "Professional")

- **B2B (`magento/module-company`, negotiable quotes, shared catalogs,
  requisition lists)** — moduł Commerce-only, nieobecny w CE. Zero
  możliwości ćwiczenia bez licencji/trial Commerce.
- **Page Builder** — dostępny też dla Open Source jako osobny composer
  package, ale nieinstalowany w tym repo i nieobecny w planie.
- **Full Page Cache / Varnish** — kontenery `compose*.yaml` w tym repo nie
  zawierają Varnisha; FPC wbudowany jest teoretycznie dostępny, ale
  nieprzetestowany.
- **Import/Export (ETL, `Magento_ImportExport`)** — brak w planie mimo że
  to częsty temat egzaminacyjny przy "data flow" i integracjach.

## Co jest mocną stroną tego repo względem egzaminu

- Etapy 1–2 (moduły, DI, service contracts, plugin/observer) i Etap 5
  (admin UI) budują naprawdę solidne podstawy pod sekcję Architecture —
  to nie jest powierzchowne, tylko realna praktyka z pułapkami
  (np. notatka o `ProductRepositoryInterface::getList()` niefiltrującym
  disabled/out-of-stock — to dokładnie typ wiedzy testowanej na egzaminie).
- Etap 7 (RabbitMQ/MessageQueue) wykracza poza to, co typowo jest testowane
  wprost w blueprincie, ale daje głębsze zrozumienie architektury niż
  wymagane minimum.
- Etap 8 (testy) i planowany Etap 10 (CI/CD) budują dobre nawyki
  inżynierskie, choć CI/CD w tej wersji nie pokrywa sekcji Cloud egzaminu.

## Rekomendacje — co rozszerzyć

**Da się zrobić w tym repo (bez zmiany edycji Magento):**
1. ~~Cron~~ — **zrobione, Etap 11** (`etc/crontab.xml` + konfigurowalna
   retencja). Zostaje stretch goal: własna grupa cron
   (`etc/cron_groups.xml`) i `lock provider`.
1b. ~~Lokalizacja / i18n~~ — **zrobione, Etap 12** (`__()`,
   `TimezoneInterface`, `i18n:collect-phrases`, `i18n/pl_PL.csv`,
   zweryfikowane end-to-end w przeglądarce). Zostaje stretch goal:
   scope store view (nie tylko `default`) i "Translate Inline".
2. Nowy etap/zadanie: **URL rewrites** — custom rewrite dla własnej
   encji/kontrolera, konflikt rewrite'ów, `NoRouteHandler`.
3. Nowy etap/zadanie: **Multi-store/website** — drugi store view, różne
   ceny/atrybuty per scope, przełączanie `?___store=`.
4. Rozszerzenie Etapu 3: **attribute sets** (tworzenie/klonowanie),
   EAV na innej encji niż produkt (np. customer).
5. Rozszerzenie Etapu 6: SOAP endpoint, async/bulk API
   (`etc/webapi_async.xml`), GraphQL mutation + rozszerzenie istniejącego
   typu (`ProductInterface`) przez `graphql_config` plugin.
6. Nowy, duży etap: **Checkout i sales** — customowy shipping method
   (`Magento_Shipping` carrier), customowy payment method (offline),
   observer na `sales_order_place_after`, layout XML dla checkout step.
7. Full Page Cache: włączyć wbudowany FPC (bez Varnisha), sprawdzić
   `CacheableInterface`, tagi cache, nagłówki debug.

**Wymaga osobnego środowiska (poza tym repo):**
1. **Adobe Commerce Cloud trial** (Adobe udostępnia trial dla partnerów/
   certyfikujących się) — jedyny sposób realnie dotknąć sekcji Cloud
   (12% egzaminu) i data flow do usług SaaS (Catalog Service, Live
   Search) z sekcji Customizations.
2. **Adobe Commerce (on-prem) trial/license** — do B2B (company accounts,
   negotiable quotes, shared catalogs) i Page Builder w wersji Commerce.
3. Rozważ oficjalny kurs przygotowawczy Adobe (`EPG-E724` / Adobe Commerce
   Developer Professional Prep Guide na `certification.adobe.com`) —
   zawiera moduły dotyczące dokładnie sekcji Cloud, których to repo nie
   jest w stanie odtworzyć.

## Podsumowanie liczbowe (szacunkowe, subiektywne)

- Sekcja Architecture (52%): ~55% pokryte przez plan (Etap 11 dodał cron),
  reszta dopisywalna w tym repo.
- Sekcja Customizations (36%): ~25–30% pokryte (głównie catalog + API),
  checkout/sales i SaaS data flow to spora, częściowo niedopisywalna luka.
- Sekcja Cloud (12%): ~0% pokryte, wymaga osobnego środowiska.

Łącznie: ukończenie **całego** obecnego planu nauki (Etapy 0–10) daje
solidne ~45–55% realnego przygotowania do egzaminu — wystarczające jako
fundament, ale **nie wystarczające samo w sobie** do zdania AD0-E724 bez
dodatkowego środowiska Adobe Commerce Cloud/Commerce i tematów
wymienionych wyżej jako "braki specyficzne dla Adobe Commerce".

---

## Źródła

- [Adobe Commerce Developer Professional — oficjalna strona certyfikacji i blueprint](https://certification.adobe.com/certification/adobe-commerce-developer-professional-v2/1242)
- [Adobe Commerce Developer Professional Prep Guide (certification.adobe.com/courses/1238)](https://certification.adobe.com/courses/1238)
- [EDUSUM — przykładowe pytania i tematy AD0-E724](https://www.edusum.com/adobe/adobe-commerce-developer-professional-ad0-e724-certification-sample-questions)
