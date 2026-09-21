# Plan nauki Magento 2

Plan oparty o istniejące środowisko: Mark Shust `docker-magento`, Magento 2.4.9 CE,
kontenery `rabbitmq`, `opensearch`/`elasticsearch`, `redis`, `db`. Własne moduły
piszemy w `src/app/code/Training/<NazwaModulu>` (jedyny fragment `src/` śledzony
przez git — patrz `.gitignore`).

Każdy etap ma: **cel**, **kluczowe pojęcia**, **zadanie praktyczne** i **kryteria
odbioru** (jak sam(a) sprawdzisz, że działa). Zadania są opisami do samodzielnej
implementacji — celowo bez gotowego kodu, żeby faktycznie się nauczyć. Jeśli utkniesz,
poproś mnie o hint albo code review konkretnego pliku, zamiast gotowca.

Sugerowane tempo: 1 etap = kilka dni pracy po godzinach. Nie ma sztywnych dat — rób
checkbox po checkboxie.

---

## Etap 0 — Rozgrzewka ze środowiskiem

**Cel:** oswoić się z narzędziami, którymi będziesz pracować przez resztę planu.

- [ ] `make start` / `make stop` / `make status` — uruchom i zatrzymaj środowisko.
- [ ] `bin/magento list` — przejrzyj dostępne komendy CLI.
- [ ] `bin/cli bash` vs `bin/bash` — zrozum różnicę (kontener phpfpm vs host).
- [ ] `bin/log` — podejrzyj logi na żywo, wywołaj celowo błąd (np. literówka w URL admina) i znajdź go w logu.
- [ ] `bin/n98-magerun2 list` — poznaj magerun jako alternatywę/dodatek do `bin/magento`.
- [ ] Zaloguj się do RabbitMQ Management UI: http://localhost:15672 (login/hasło w `env/rabbitmq.env`, domyślnie `magento`/`magento`). Zobacz zakładki Queues, Exchanges.

**Kryterium odbioru:** potrafisz odpalić dowolną komendę Magento CLI i poprawnie
zinterpretować błąd z `bin/log` bez googlowania każdego kroku.

---

## Etap 1 — Anatomia modułu

**Cel:** zrozumieć strukturę modułu Magento 2 i cykl życia `setup:upgrade`.

**Zadanie:** stwórz moduł `Training_HelloWorld` w `src/app/code/Training/HelloWorld`:
- `etc/module.xml`, `registration.php`.
- Kontroler frontendowy `Training\HelloWorld\Controller\Index\Index`, dostępny pod
  `/helloworld` (potrzebny `etc/frontend/routes.xml`).
- Prosty layout XML + blok + template `.phtml`, które wypisują "Hello, Magento!"
  oraz aktualną datę.
- Komenda konsolowa `bin/magento training:hello` (klasa w `Console/Command`,
  zarejestrowana w `etc/di.xml` przez `Magento\Framework\Console\CommandListInterface`).

**Kryteria odbioru:**
- `bin/magento module:status` pokazuje moduł jako enabled.
- `/helloworld` renderuje stronę bez błędów w logu.
- `bin/magento training:hello` działa z linii poleceń.

---

## Etap 2 — Konfiguracja XML, DI i Service Contracts

**Cel:** zrozumieć `di.xml` (preference, plugin, virtualType, argumenty), oraz wzorzec
Service Contract (Interface + Repository + Data Interface).

**Zadanie:** rozbuduj `Training_HelloWorld` (albo nowy moduł `Training_Greeting`) o:
- Własną tabelę `training_greeting` (declarative schema `etc/db_schema.xml`,
  nie stary InstallSchema!) z kolumnami: id, message, created_at.
- `GreetingInterface` (Data Interface) + `GreetingRepositoryInterface` +
  implementacja `GreetingRepository` używająca `ResourceModel` + `Collection`.
- Rejestracja w `etc/di.xml` (`preference` dla interfejsu na implementację).
- Plugin (`around`/`after`) na dowolną natywną klasę core'ową (np.
  `Magento\Catalog\Model\Product::getName()` — dopisz sufiks do nazwy produktu w plugin
  `after`), zarejestrowany w `etc/frontend/di.xml`.
- Observer na event `catalog_product_save_after`, który zapisuje wpis do
  `training_greeting` przy każdym zapisie produktu.

**Kryteria odbioru:**
- `bin/magento setup:upgrade` tworzy tabelę bez błędów, widoczna przez `bin/mysql`.
- Zmiana nazwy produktu w adminie faktycznie odpala plugin (widać efekt na froncie).
- Zapis produktu tworzy nowy wpis w tabeli (obserwator działa).
- Umiesz wytłumaczyć różnicę: plugin `before`/`after`/`around` vs. observer — kiedy
  użyć czego.

---

## Etap 3 — Katalog, EAV i repozytoria

**Cel:** zrozumieć model EAV, indeksowanie i pułapki `ProductRepositoryInterface`.

**Zadanie:**
- Napisz `Setup\Patch\Data` (declarative data patch), który tworzy nowy atrybut
  produktu (np. `training_badge`, typ select z opcjami "Nowość"/"Promocja"/"Hit"),
  dodaje go do domyślnego zestawu atrybutów i czyni go filtrowalnym w warstwie
  nawigacji.
- Komenda CLI `bin/magento training:products:tag`, która przez
  `ProductRepositoryInterface` + `SearchCriteriaBuilder` znajduje produkty spełniające
  warunek (np. cena > X) i ustawia im atrybut `training_badge`.
- Sprawdź świadomie (empirycznie, nie z dokumentacji!): czy Twoja komenda widzi
  produkt, który jest `disabled` albo ma `qty=0`? **Zweryfikowane 2026-09-14 na tej
  instalacji Magento 2.4.9: TAK, widzi oba** — `getList()` nie filtruje po
  statusie ani stanie magazynowym, wbrew powszechnemu przekonaniu (i wbrew temu,
  co wcześniej mylnie mówiła notatka w pamięci projektu — patrz
  `CLAUDE.md` sekcja "Konwencje projektu"). Jeśli chcesz to wykluczyć, musisz dodać
  filtr jawnie.

**Kryteria odbioru:**
- Atrybut widoczny w adminie i filtrowalny na froncie (po reindexie).
- Umiesz opisać różnicę między `ProductRepositoryInterface::getList()` a bezpośrednim
  zapytaniem przez `CollectionFactory` pod kątem tego, co i dlaczego jest widoczne.

---

## Etap 4 — Indeksery i cache

**Cel:** zrozumieć tryby indeksowania (`update on save` / `update by schedule`) oraz
warstwy cache Magento.

**Zadanie:**
- Zamień tryb indeksera `catalog_product_price` (albo innego) na `schedule` i
  obserwuj różnicę w zachowaniu po edycji produktu.
- Napisz własny prosty indekser (`Training_Greeting`: licznik pozdrowień per produkt)
  zarejestrowany w `etc/indexer.xml`, z tabelą `training_greeting_count` budowaną
  przez `Indexer\Action\Full` i `Indexer\Action\Row`.
- Wywołaj `bin/magento indexer:reindex training_greeting_count` i `indexer:status`.
- Wyczyść cache blokowy (`bin/magento cache:clean block_html`) i sprawdź czym się to
  różni od `cache:flush`.

**Kryteria odbioru:** potrafisz wytłumaczyć różnicę invalid/reindex required oraz
`cache:clean` vs `cache:flush` bez zaglądania do dokumentacji.

---

## Etap 5 — Warstwa admina (ACL, menu, UI Components)

**Cel:** zbudować pełny ekran administracyjny CRUD dla własnej encji.

**Zadanie:** dla encji `Greeting` z Etapu 2 zbuduj w adminie:
- Pozycję w menu + ACL resource (`etc/acl.xml`, `etc/adminhtml/menu.xml`).
- Grid listy (UI Component `etc/adminhtml/training_greeting_listing.xml` +
  DataProvider).
- Formularz dodaj/edytuj (UI Component form) zapisujący przez
  `GreetingRepositoryInterface`.
- Akcję "Delete" z potwierdzeniem.

**Kryteria odbioru:** pełny CRUD działa z poziomu adminhtml, użytkownik bez
przypisanego ACL resource nie widzi pozycji menu.

---

## Etap 6 — API: REST i GraphQL

**Cel:** wystawić Service Contract z Etapu 2 jako REST i GraphQL.

**Zadanie:**
- `etc/webapi.xml` wystawiający `GreetingRepositoryInterface` pod
  `/V1/training/greetings`.
- Test przez `bin/cli curl` albo Postman z tokenem admina
  (`bin/magento security:token:generate` / integracja OAuth — dowolna metoda auth).
- Prosty resolver GraphQL (`etc/schema.graphqls` + klasa resolvera) zwracający listę
  greetingów.

**Kryteria odbioru:** zapytanie REST i zapytanie GraphQL zwracają te same dane co
grid w adminie.

---

## Etap 7 — Message Queue: symulacja integracji z PIM (capstone)

**Cel:** zrozumieć całą architekturę `MessageQueue` w Magento (topic, exchange,
queue, consumer, publisher) na scenariuszu zbliżonym do realnej integracji z PIM:
zewnętrzny system publikuje wiadomości o zmianach produktów, Magento je konsumuje i
aktualizuje katalog.

RabbitMQ jest już uruchomiony w `compose.yaml` (kontener `rabbitmq`,
`markoshust/magento-rabbitmq`). Magento domyślnie w tym obrazie jest już
skonfigurowane do korzystania z AMQP (sprawdź `src/app/etc/env.php` sekcję `queue`).

### 7.1 — Teoria (przed kodowaniem)

Zanim zaczniesz pisać kod, odpowiedz sobie pisemnie (np. w komentarzu na górze
głównej klasy) na pytania:
- Czym różni się `topic` od `queue` od `exchange` w AMQP/Magento MessageQueue?
- Co robi `queue_topology.xml`, a co `queue_consumer.xml`, a co `communication.xml`?
- Czym różni się konsument uruchamiany przez `queue:consumers:start` od
  `cron_run` / `queue:consumers:start --max-messages=N` w crontabie?

### 7.2 — Moduł `Training_PimSync`

Stwórz moduł symulujący integrację z zewnętrznym PIM. Zakres:

1. **Kontrakt komunikacji** — `etc/communication.xml`: zdefiniuj topic
   `training.pim.product.sync` z typem danych `Training\PimSync\Api\Data\PimProductMessageInterface`
   (pola: `sku`, `name`, `price`, `qty`, `status` — jako DTO, nie tablica asocjacyjna).
2. **Topologia** — `etc/queue_topology.xml`: exchange `training.pim` (typ topic),
   binding topicu do kolejki `training.pim.product.sync.queue`.
3. **Konsument** — `etc/queue_consumer.xml` + klasa `Consumer/PimProductSyncConsumer`
   implementująca metodę `process(PimProductMessageInterface $message)`:
   - jeśli SKU istnieje → aktualizuj `price`, `qty` (przez `StockRegistryInterface` lub
     `SourceItemsSaveInterface` — zależnie od tego czy MSI jest włączone), `status`;
   - jeśli SKU nie istnieje → utwórz nowy prosty produkt;
   - loguj każdą operację przez `Psr\Log\LoggerInterface` do osobnego kanału
     (`etc/di.xml` virtualType na `Monolog\Logger` + handler do
     `var/log/pim_sync.log`).
4. **Publisher (symulator PIM)** — komenda CLI `bin/magento training:pim:simulate
   [--count=N]`, która przez `PublisherInterface::publish()` publikuje N losowych
   wiadomości (losowe SKU z puli, losowa cena/qty/status) na topic
   `training.pim.product.sync`. To ona odgrywa rolę "zewnętrznego PIM-a".
5. **Obsługa błędów** — celowo wygeneruj złą wiadomość (np. ujemna cena albo brak
   SKU) i zaimplementuj sensowną walidację w konsumerze, która rzuca
   wyjątek zamiast dodać śmieciowy produkt. Sprawdź co się dzieje z taką wiadomością
   w RabbitMQ (podgląd w Management UI — czy trafia do jakiegoś dead-letter, czy
   zostaje zaacknowledgowana mimo błędu — od tego zależy czy wiadomość wraca do
   kolejki w pętli).

### 7.3 — Uruchomienie i weryfikacja

- `bin/magento queue:consumers:list` — Twój konsument powinien być widoczny.
- Terminal 1: `bin/magento queue:consumers:start pimProductSyncConsumer`
  (zostaw działający, obserwuj output).
- Terminal 2: `bin/magento training:pim:simulate --count=20`.
- W RabbitMQ Management UI obserwuj wykres "Message rates" na kolejce podczas
  publikacji i konsumpcji.
- Sprawdź w adminie / `bin/magento training:pim:simulate`, że produkty faktycznie
  się zaktualizowały (ceny, ilości).
- Zatrzymaj konsumenta (Ctrl+C), opublikuj kolejne wiadomości, sprawdź że siedzą w
  kolejce (Management UI pokazuje "Ready"), potem odpal konsumenta ponownie i
  zobacz jak je "dojada".

### 7.4 — Rozszerzenie (opcjonalnie, dla utrwalenia)

- Dodaj drugi topic `training.pim.category.sync` i drugą kolejkę, żeby zobaczyć jak
  wygląda routing wielu topiców przez jeden exchange.
- Skonfiguruj konsumenta do uruchamiania przez cron (`etc/crontab.xml` +
  `bin/magento cron:run`) zamiast ręcznie w terminalu — tak wygląda to na produkcji.
- Dodaj `max_messages` / batch size i zobacz wpływ na throughput przy
  `training:pim:simulate --count=1000`.

**Kryteria odbioru (capstone):**
- Umiesz narysować (choćby na kartce) pełny przepływ: publisher → exchange → binding
  → queue → consumer → repository → baza danych, i nazwać plik XML odpowiedzialny za
  każdą strzałkę.
- Potrafisz celowo zepsuć wiadomość i przewidzieć, co zrobi RabbitMQ/Magento, zanim
  to sprawdzisz.
- Rozumiesz, dlaczego message queue to lepsze rozwiązanie dla integracji z PIM niż
  synchroniczne wywołanie REST API przy imporcie hurtowym danych.

---

## Etap 8 — Testy i jakość kodu

**Cel:** nauczyć się testować to, co zbudowałeś w Etapach 1–7, i korzystać z
istniejących narzędzi w `bin/`.

**Zadanie:**
- Unit test dla logiki walidacji z `PimProductSyncConsumer` (namierz i wydziel
  logikę walidacyjną do osobnej, łatwo testowalnej klasy).
- Integration test dla `GreetingRepository` (`bin/dev-test-run integration`) —
  wymaga wcześniej `bin/setup-integration-tests`.
- Uruchom `bin/phpcs` i `bin/analyse` na całym `Training/` i wyzeruj zgłoszenia.

**Kryteria odbioru:** `bin/dev-test-run unit` i `bin/phpcs` przechodzą bez błędów
dla wszystkich modułów `Training/*`.

---

## Etap 9 — Frontend głębiej (Luma)

Ten projekt stoi na domyślnym, natywnym froncie Magento (Luma) — brak zainstalowanej
Hyvä (`composer.json` nie ma pakietów `hyva-themes/*`, w `app/design/frontend/` są
tylko pakietowe motywy `Magento/*`). Zadania robimy na Luma:

- [ ] Stwórz child theme `src/app/design/frontend/Training/theme` (dziedziczący po
  `Magento/luma`), zarejestruj go i ustaw jako domyślny motyw sklepu.
- [ ] Nadpisz jeden natywny `.phtml` (np. header albo footer) w child theme i zmień
  w nim coś widocznego.
- [ ] Dodaj do niego prosty komponent JS oparty o natywny stack Magento
  (`requirejs` + `uiComponent`/`uiClass`, lub KnockoutJS `data-bind`) — np. licznik
  kliknięć zapisywany przez fetch do Twojego REST endpointu z Etapu 6.
- [ ] Dodaj własny plik LESS (`web/css/source/_module.less` + import w
  `_extend.less`) i przelicz go przez `bin/magento setup:static-content:deploy`
  (albo `bin/setup-grunt` jeśli wolisz Grunt+LESS watch).

**Kryterium odbioru:** strona działa bez błędów konsoli, Twój child theme jest
aktywny (`bin/magento config:show design/theme/theme_id` wskazuje na niego), styl
i JS budują się bez błędów.

**Stretch goal (opcjonalnie):** jeśli chcesz też poznać Hyvä, zainstaluj darmowy
`hyva-themes/magento2-default-theme` (od listopada 2025 open-source) obok tego
etapu jako osobne ćwiczenie porównawcze — ale to świadomy, oddzielny krok, nie
założenie tego etapu.

---

## Etap 10 — CI/CD: GitHub Actions dla Magento

**Cel:** zbudować realistyczny pipeline CI, jaki spotkasz w produkcyjnych projektach
Magento — warstwowo, od szybkich sprawdzeń do pełnego builda produkcyjnego.

Projekt ma już zdalne repo na GitHubie (`origin`), więc workflow możesz od razu
wypychać i patrzeć jak faktycznie działa w zakładce Actions, a nie tylko sprawdzać
składnię YAML lokalnie.

**Kluczowe pojęcia:** `services:` w GitHub Actions (kontenery pomocnicze jak MySQL/
OpenSearch/RabbitMQ dostępne dla joba), macierz (`strategy.matrix`) do testowania
kilku wersji PHP, cache zależności (`actions/cache` na `~/.composer/cache`),
`environments` + required reviewers do bramkowania deployu, różnica między
"testować w dev mode" a "budować w production mode" (`setup:di:compile` +
`setup:static-content:deploy`).

**Zadanie:**

1. **Job `lint`** (`.github/workflows/ci.yml`) — bez Dockera, bezpośrednio na
   runnerze: `composer validate`, `composer install`, `vendor/bin/phpcs` z
   Magento2 ruleset dla `src/app/code/Training`. Uruchamiany na każdy push i PR.
2. **Job `unit-tests`** — matrix po 2 wersjach PHP (np. 8.3 i 8.4), instaluje
   zależności przez composer (bez Dockera z tego repo — to osobna instalacja
   zależności na runnerze, nie kontener `phpfpm`), odpala `vendor/bin/phpunit` dla
   `Test/Unit` z Twoich modułów `Training/*`.
3. **Job `integration-tests`** — dodaj `services:` z obrazem `mysql:8` (i
   opcjonalnie `rabbitmq` jeśli chcesz przetestować `Training_PimSync`), poczekaj aż
   usługa będzie gotowa (`options: --health-cmd`), zainstaluj minimalną Magento
   (albo użyj gotowego obrazu z Magento już zainstalowanym w warstwie builda) i
   odpal `dev/tests/integration` dla Twoich modułów.
4. **Job `build`** (zależny od poprzednich, `needs: [lint, unit-tests]`) —
   symuluje realny build produkcyjny: `composer install --no-dev
   --optimize-autoloader`, `bin/magento setup:di:compile`,
   `bin/magento setup:static-content:deploy -f`. Celowo wprowadź błąd w jednym z
   Twoich pluginów z Etapu 2 (np. zła sygnatura metody) i zobacz, czy `build` go
   złapie, mimo że lokalnie w dev mode wszystko działało.
5. **(Opcjonalnie) Job `docker-build`** — zbuduj obraz Docker z gotowym kodem
   (multi-stage Dockerfile: stage builder z composerem/node, stage runtime tylko z
   wynikiem), otaguj commit SHA, wypchnij do dowolnego darmowego registry (np.
   GitHub Container Registry `ghcr.io`) — użyj `secrets.GITHUB_TOKEN`, nic dodatkowo
   nie musisz konfigurować.
6. **(Opcjonalnie) Job `deploy-staging`** — atrapa deployu: krok, który tylko
   wypisuje "deploying commit X to staging" i wymaga zatwierdzenia przez
   GitHub Environment z required reviewer. Cel: poćwiczyć mechanizm bramkowania
   deployu, nie budować prawdziwej infrastruktury.

**Kryteria odbioru:**
- Workflow widoczny w zakładce Actions po pushu, wszystkie joby `lint`,
  `unit-tests`, `build` przechodzą na zielono na czystym kodzie.
- Umiesz celowo zepsuć coś, co złapie tylko `build` (nie `lint` ani `unit-tests`) —
  i wytłumaczyć dlaczego akurat ten etap to złapał.
- Rozumiesz, dlaczego `integration-tests` i pełny `build` zwykle nie odpala się na
  każdy commit w dużych zespołach, tylko na PR do głównej gałęzi / przed release'em
  (koszt czasu CI).

---

## Etap 11 — Cron (dodatek pod certyfikację AD0-E724)

**Cel:** zrozumieć architekturę Magento Cron — `crontab.xml`, tabelę
`cron_schedule`, konfigurowalność przez system config i scope (store/website)
— temat pokrywany w sekcji "Architecture" egzaminu Adobe Commerce Developer
Professional, wcześniej niepokryty w tym planie (patrz
`docs/adobe-commerce-developer-professional-gap-analysis.md`).

**Zadanie:** rozbudowano `Training_Greeting` o cykliczne czyszczenie starych
wpisów z `training_greeting`:
- `etc/crontab.xml` — job `training_greeting_cleanup` w grupie `default`,
  harmonogram raz dziennie o 3:00 (`0 3 * * *`), wskazujący na
  `Training\Greeting\Cron\CleanupOldGreetings::execute`.
- `Cron/CleanupOldGreetings.php` — **logika napisana i zweryfikowana
  empirycznie** (wywołanie `execute()` bezpośrednio przez bootstrap
  Magento, na realnych danych w `training_greeting`). Po drodze złapany i
  naprawiony realny bug: `retention_days <= 0` musi być jawnie
  zablokowane, bo `retention_days = 0` liczyło próg jako "teraz" i kasowało
  całą tabelę (`< 0` zamiast `<= 0` w pierwszej wersji). Próg czasowy liczony
  jawnie w UTC (`created_at` w DB to timestamp w UTC, nie w strefie
  serwera PHP).
- `etc/adminhtml/system.xml` + `etc/config.xml` — pole "Retention (days)"
  w Stores > Configuration > Training Greeting > General, domyślnie 30,
  konfigurowalne per store/website (scope!).

**Kryteria odbioru:**
- ✅ **Zweryfikowane przez faktyczny scheduler** (nie tylko bezpośrednie
  `execute()`): wpis w `cron_schedule` przechodzi przez `bin/magento
  cron:run --group="default"` ze statusem `success`. Po drodze złapany
  efekt uboczny testowania: config cache (`config: 1`) trzyma starą
  wartość configu do jawnego `bin/magento cache:flush config` — usunięcie
  wiersza z `core_config_data` samo w sobie nie unieważnia cache'u.
- ✅ Zmiana wartości retencji faktycznie wpływa na to, które wiersze są
  usuwane — potwierdzone na dwóch ręcznie wstawionych wierszach (40 dni i
  1 dzień wstecz, retencja 30 dni): usunięty tylko starszy.
- Umiesz wytłumaczyć różnicę między `schedule_generate_every` /
  `schedule_ahead_for` / `schedule_lifetime` / `history_cleanup_every`
  (domyślne ustawienia grupy `default` w core'owym `etc/config.xml`
  Magento_Cron) a tym, co by się zmieniło, gdybyś stworzył **własną grupę
  cron** (`etc/cron_groups.xml`) zamiast używać `default`.
- (Stretch, opcjonalnie) Stwórz własną grupę cron `training_group` w
  `etc/cron_groups.xml`, z osobnym `use_separate_process`, i przenieś tam
  job — zaobserwuj różnicę w generowaniu harmonogramu dla tej grupy
  względem `default`.

---

## Etap 12 — Lokalizacja / i18n (dodatek pod certyfikację AD0-E724)

**Cel:** zrozumieć mechanizm tłumaczeń Magento — `__()` w PHP i szablonach,
pliki `i18n/<locale>.csv`, CLI `i18n:collect-phrases`, konfigurację locale
per store view (`general/locale/code`) oraz formatowanie dat zależne od
locale (`TimezoneInterface`) — temat pokrywany w sekcji "Architecture"
egzaminu Adobe Commerce Developer Professional, dotąd niepokryty w tym
planie (patrz `docs/adobe-commerce-developer-professional-gap-analysis.md`).

**Szkielet już wygenerowany** w `Training_HelloWorld`:
- `Block/Greeting.php` — wstrzyknięty `TimezoneInterface`, `getGreeting()` i
  `getCurrentDate()` zostawione z `// TODO` (string niewowinięty w `__()`,
  data liczona gołym `DateTime::format()` zamiast `formatDateTime()`).
- `view/frontend/templates/greeting.phtml` — dodany statyczny label
  "Current date" z `TODO` do owinięcia w `__()` bezpośrednio w szablonie.
- Empirycznie sprawdzone: `/helloworld` renderuje się (200, DI działa) —
  szkielet nie psuje strony, tylko nie tłumaczy jeszcze niczego.

**Zadanie (do Ciebie):**
1. Uzupełnij TODO w `Block/Greeting.php` i `greeting.phtml` — owiń stringi
   w `__()`.
2. Przepisz `getCurrentDate()` na `$this->timezone->formatDateTime(...)`.
3. Uruchom `bin/magento i18n:collect-phrases -o dictionary.csv
   app/code/Training/HelloWorld` i zobacz, jak Magento sam wyłapuje frazy
   do przetłumaczenia z kodu.
4. Stwórz `i18n/pl_PL.csv` w module (klucz = oryginalna fraza z `__()`,
   wartość = tłumaczenie; format CSV bez nagłówka kolumn) z polskim
   tłumaczeniem znalezionych fraz.
5. Przełącz locale store view na `pl_PL` (`bin/magento config:set
   general/locale/code pl_PL --scope=stores --scope-code=default` albo w
   adminie: Stores > Configuration > General > Locale Options), zrób
   `cache:flush`, i sprawdź `/helloworld` na froncie.
6. (Stretch) Włącz "Translate Inline" (Stores > Configuration > Developer >
   Translate Inline, wymaga trybu developer), zobacz jak edytuje się
   tłumaczenia bezpośrednio z frontu — potem **koniecznie wyłącz z
   powrotem** (zostawione włączone potrafi zepsuć cache/front).

**Kryteria odbioru:**
- ✅ `/helloworld` z locale `pl_PL` pokazuje polskie teksty z
  `i18n/pl_PL.csv` ("Witaj, Magento!" / "Aktualna data") — zweryfikowane
  na żywo przez `bin/magento config:set general/locale/code pl_PL` +
  `cache:flush` + render strony. Z powrotem na `en_US` (bez pliku CSV) —
  fallback do oryginalnych angielskich stringów, też potwierdzone.
  Uwaga: to był test na scope `default` (całe środowisko), nie na
  konkretnym store view — jeśli chcesz przećwiczyć też
  `--scope=stores --scope-code=default` i różnicę między poziomami scope,
  to zostaje do zrobienia (wiąże się z przyszłym zadaniem "multi-store" z
  analizy luk).
- ✅ Data wyświetla się w formacie zależnym od locale — potwierdzone
  wizualnie: `pl_PL` → "17 wrz 2026, 16:05", `en_US` → "Sep 17, 2026, 4:05
  PM".
- Umiesz wytłumaczyć, dlaczego `__()` owija string zamiast bezpośredniej
  konkatenacji zmiennej w środku (podpowiedź: placeholdery `%1`/`%2` i
  kolejność argumentów przy tłumaczeniu na język o innym szyku zdania).
- Rozumiesz różnicę między plikiem `i18n/<locale>.csv` (tłumaczenia
  modułu, deployowane z kodem) a "Translate Inline" (edycja zapisana w
  bazie per scope, do szybkich poprawek, nie do produkcji).

---

## Etap 13 — URL rewrites (dodatek pod certyfikację AD0-E724)

**Cel:** zrozumieć encję `url_rewrite` — tabelę, unique constraint
`(request_path, store_id)`, różnicę między cichym rewrite (`redirect_type =
0`, URL w pasku przeglądarki się nie zmienia) a przekierowaniem (301/302,
`Location` w odpowiedzi), oraz `UrlPersistInterface` jako API do zapisu —
temat pokrywany w sekcji "Architecture" egzaminu Adobe Commerce Developer
Professional, dotąd niepokryty w tym planie (patrz
`docs/adobe-commerce-developer-professional-gap-analysis.md`).

**Zrobione** w `Training_HelloWorld`:
- `etc/module.xml` — dodana `<sequence>` na `Magento_UrlRewrite` (patch
  używa jego `UrlPersistInterface`/`UrlRewriteFactory`).
- `Setup/Patch/Data/AddHelloWorldUrlRewrites.php` — data patch dopisujący
  dwa wpisy `entity_type='custom'`: `witaj` (cichy, `redirect_type=0`) i
  `stare-hello` (301, `OptionProvider::PERMANENT`), oba
  `target_path='helloworld'`, `store_id` ze `StoreManagerInterface`.

**Co wyszło z empirycznej weryfikacji (warte zapamiętania):**
- `target_path` celowo to sam frontName `helloworld`, nie pełne
  `helloworld/index/index`. Oba routują tak samo, ale przy
  `redirect_type != 0` Magento wstawia `target_path` **wprost w nagłówek
  `Location`** — pełna ścieżka wyciekłaby userowi i to ona zostałaby w
  pasku przeglądarki.
- `UrlPersistInterface::replace()` **nie** rzuca wyjątku, gdy nadpisujesz
  rewrite tej samej encji — najpierw kasuje wszystkie rewrite'y o danym
  `entity_type`+`entity_id`+`store_id`, potem wstawia to, co dostał.
  Ponieważ oba nasze wpisy mają `custom`/`0`/`1`, wywołanie
  `replace([$tylko_jeden])` po cichu skasowałoby ten drugi. Dlatego patch
  przekazuje oba w jednym wywołaniu.
- `UrlAlreadyExistsException` ("URL key for specified store already
  exists.") leci dopiero wtedy, gdy o ten sam `request_path`+`store_id`
  bije się **inna** encja (inne `entity_id`) — to wtedy gryzie unique
  constraint `URL_REWRITE_REQUEST_PATH_STORE_ID`.
- ⚠️ Data patch jest oznaczany jako "zastosowany" w `patch_list`
  niezależnie od tego, czy `apply()` cokolwiek zrobił — pusty `apply()` +
  `setup:upgrade` = patch spalony, logika dopisana później się nie
  wykona. Ratunek: `DELETE FROM patch_list WHERE patch_name LIKE
  '%AddHelloWorldUrlRewrites%'` i `setup:upgrade` jeszcze raz.

**Do dokończenia (opcjonalnie):**
1. (Katalog) Zmień URL key istniejącego produktu w adminie (Catalog >
   Products) i sprawdź w `url_rewrite`, czy stary URL dostał automatycznie
   wygenerowany rewrite z przekierowaniem do nowego — zależy od opcji
   "Create Permanent Redirect for old URL" w Stores > Configuration >
   Catalog > Catalog > Search Engine Optimization.
2. Obejrzyj Marketing > URL Rewrites w adminie — te same wiersze, które
   dopisał patch, są tam edytowalne z GUI.

**Kryteria odbioru:**
- ✅ `/witaj` → `200`, bez `Location`, renderuje treść bloku
  (`Hello, Magento!`), URL w pasku zostaje `/witaj`.
- ✅ `/stare-hello` → `301` z `Location: https://magento.test/helloworld`.
- ✅ `/this-does-not-exist` → `404` (`NoRouteHandler`), konfigurowalny w
  Stores > Configuration > General > Web > Default Pages > CMS No Route
  Page.
- Umiesz wytłumaczyć różnicę między rewrite'em custom (ręcznie wpisany,
  `is_autogenerated=0`) a autogenerowanym rewrite'em produktu/kategorii —
  kto go tworzy i kiedy (save encji, nie reindex na żądanie).

---

## Etap 14 — Full Page Cache (dodatek pod certyfikację AD0-E724)

**Cel:** zrozumieć, jak FPC decyduje, co i na jak długo cache'uje, jak strony
są tagowane i jak się je unieważnia — temat pokrywany w sekcji
"Architecture" egzaminu Adobe Commerce Developer Professional, dotąd
pokryty tylko częściowo (Etap 4 dotknął wyłącznie cache blokowego; patrz
`docs/adobe-commerce-developer-professional-gap-analysis.md`).

**Środowisko:** wbudowany FPC (`caching_application` = built-in, backend
Redis), tryb developer. **Brak Varnisha** w `compose*.yaml` — Etap uczy
mechanizmu (tagi, identities, unieważnianie), który jest wspólny dla obu
trybów; różnice built-in vs Varnish to część stretch goala.

**Jak sprawdzać (ważne):** nagłówki odpowiedzi.

```bash
curl -sk -D - --resolve magento.test:443:127.0.0.1 https://magento.test/helloworld -o /dev/null \
  | grep -iE "^HTTP|x-magento-cache-debug|x-magento-tags|cache-control"
```

- `--resolve` jest potrzebne: `curl https://localhost/...` dostaje `302` na
  `magento.test` (Magento wymusza skonfigurowany host), więc mierzysz
  przekierowanie, nie cache. W zsh nie wkładaj `--resolve ...` do zmiennej —
  nie rozbije się na osobne argumenty; wpisuj go wprost.
- `X-Magento-Cache-Debug`: `MISS` (strona wygenerowana i zapisana), `HIT`
  (z cache). Brak tego nagłówka = odpowiedź nie wchodzi w ogóle do FPC.
- `X-Magento-Tags` jest widoczny tylko w trybie developer (na produkcji tagi
  trafiają do nagłówka wyłącznie dla Varnisha).

**Punkt wyjścia (zmierzony na tym środowisku):** `/helloworld` wyświetla
"Current date". Po `cache:clean full_page` pierwsze żądanie to `MISS` z datą
`4:11 PM`; to samo żądanie 65 s później to `HIT` **nadal z `4:11 PM`** (na
serwerze było już `4:12`). FPC zamroził stronę na cały TTL (`max-age=86400`).

**Szkielet już wygenerowany:**
- `Training_HelloWorld`: `etc/module.xml` (sequence na `Training_Greeting`),
  `Block/LatestGreetings.php` (blok z listą 3 ostatnich wpisów Greeting,
  implementuje `IdentityInterface`, metody z `// TODO`),
  `view/frontend/templates/latest_greetings.phtml` (gotowy),
  `view/frontend/layout/helloworld_index_index.xml` (blok dodany, z
  komentarzem do eksperymentu B).
- `Training_Greeting`: `Model/Greeting.php` implementuje teraz
  `IdentityInterface` (stała `CACHE_TAG`, `getIdentities()` zwraca na razie
  `[]` — do uzupełnienia).
- Stan wyjściowy jest **celowo "zepsuty"**: strona renderuje się poprawnie
  (`200`, `MISS` → `HIT`), ale `X-Magento-Tags` nie zawiera żadnego tagu
  Greeting, więc żaden zapis wpisu jej nie unieważni.

**Zadanie (do Ciebie):**

**A. Diagnostyka (bez kodu).**
1. Sprawdź `MISS` → `HIT` na `/helloworld` i na stronie głównej. Odczytaj
   `X-Magento-Tags`: skąd biorą się tagi `cat_c_*` i `cms_b`? Który blok
   strony za nie odpowiada? Dlaczego nie ma tagu Twojego bloku?
2. Zreprodukuj zamrożoną datę (jak w "Punkcie wyjścia").
3. Porównaj `bin/magento cache:clean full_page`, `cache:flush` i
   `cache:clean config` — po którym z nich następne żądanie to `MISS`, a po
   którym nadal `HIT`? Wyjaśnij dlaczego.

**B. Eksperyment `cacheable="false"`.**
1. Dopisz `cacheable="false"` do bloku `training.helloworld.greeting` w
   layoucie (miejsce zaznaczone komentarzem), `cache:flush`.
2. Zmierz: czy data przestała się zamrażać? Co z `X-Magento-Cache-Debug`,
   `Cache-Control` i `X-Magento-Tags`? Czy strona główna też ucierpiała?
   (Zmierzone przy przygotowaniu zadania: każde żądanie `MISS`,
   `Cache-Control: no-store`, tagi spadają do samego `FPC` — czyli
   **jeden** niecache'owalny blok wyłącza cache **całej** strony; strona
   główna bez zmian.)
3. Cofnij zmianę. Wyjaśnij, dlaczego to rozwiązanie jest złe dla realnego
   sklepu, mimo że "działa".

**C. Unieważnianie po tagach (właściwy kod).** ✅ *Kod zrobiony i zweryfikowany
(`getGreetings()` Twój, `getIdentities()` w bloku i modelu razem z Claude).
Zostają pomiary z punktu 3 i eksperyment kontrolny z punktu 4 do samodzielnego
powtórzenia.*
1. `Block/LatestGreetings::getGreetings()` — zapytanie o 3 ostatnie wpisy
   (kroki w docblocku).
2. `Block/LatestGreetings::getIdentities()` i
   `Model/Greeting::getIdentities()` — zwróć tagi tak, żeby **i edycja
   istniejącego wpisu, i dodanie nowego** unieważniały stronę. Pytanie w
   docblocku jest kluczowe — zanim napiszesz, odpowiedz na nie sam(a).
3. Zweryfikuj każdy scenariusz (oczekiwane wyniki zmierzone przy
   przygotowaniu zadania, na działającym rozwiązaniu):
   - `X-Magento-Tags` zawiera teraz Twój tag listy.
   - Zapis wpisu przez repozytorium (admin: Training Greetings → Add New;
     zapisuje przez `GreetingRepositoryInterface`) → następne żądanie
     `/helloworld` to `MISS` i widać nowy wpis.
   - Strona główna po takim zapisie nadal `HIT` — unieważniana jest tylko
     strona z Twoim tagiem, nie cały FPC.
   - Wpis dodany **surowym SQL** (`bin/mysql`, `INSERT INTO
     training_greeting ...`) → `/helloworld` nadal `HIT` **bez** nowego
     wpisu. Dlaczego? (Podpowiedź: co emituje event `clean_cache_by_tags`
     i kto go woła.)
   - Usunięcie wpisu przez repozytorium → `MISS`; przy okazji zniknie też
     "zalegający" wpis z surowego SQL, bo strona zostanie wygenerowana od
     nowa.
   - Zapis dowolnego produktu w adminie też unieważnia stronę — observer z
     Etapu 2 tworzy wtedy wpis Greeting. Zauważ ten efekt uboczny.
4. **Eksperyment kontrolny:** zostaw tylko tagi per-wpis
   (`training_greeting_<id>`) i bez tagu listy. Zmierzone: edycja
   istniejącego wpisu unieważnia stronę (`MISS`), ale **dodanie nowego —
   nie** (`HIT`, brak nowego wpisu). Wyjaśnij dlaczego, i przywróć poprawną
   wersję.

**D. Pytanie otwarte (kod opcjonalny).** Data z eksperymentu B nadal jest
problemem, a `cacheable="false"` odpada. Wskaż i uzasadnij co najmniej dwa
lepsze sposoby (podpowiedzi do rozważenia: renderowanie po stronie
przeglądarki w JS; sekcje private content / `customer-data` i
`etc/frontend/sections.xml`; ESI — i dlaczego ESI wymaga Varnisha, a nie
działa w trybie built-in; skrócenie TTL — i dlaczego to tępe narzędzie).
Stretch: zaimplementuj wersję z JS.

✅ *Wersja z JS zrobiona i sprawdzona w przeglądarce* (`Block/Greeting::getDateConfig()`,
`view/frontend/web/js/current-date.js`, `greeting.phtml`): serwer wysyła pusty
`<time data-mage-init="...">`, a moduł AMD wpisuje datę przez `Intl.DateTimeFormat`
z locale i strefą **sklepu** (nie odwiedzającego). Zmierzone: przeglądarka w
`Europe/Warsaw` pokazała czas `America/New_York` (strefa sklepu), strona przez cały
test była `HIT`, a po ponad minucie data przesunęła się z `9:41 AM` na `9:43 AM`.
Serwerowo wyrenderowanej daty celowo nie ma — zamrożona wartość mignęłaby przed
uruchomieniem JS, a bez JS byłaby po prostu błędna. Locale i strefa siedzą w
scache'owanym HTML-u, więc zmiana ustawień strefy wymaga wyczyszczenia FPC
(`cache:clean config` go nie rusza — punkt A.3; to wniosek z pomiaru, nie
sprawdzony bezpośrednio).

**E. Stretch: VCL.** `bin/magento varnish:vcl:generate` działa bez
uruchomionego Varnisha (zweryfikowane). Przeczytaj wynik i znajdź: gdzie
Varnish unieważnia strony po tagach (`ban(... X-Magento-Tags-Pattern)`),
gdzie zróżnicowany jest klucz cache (`X-Magento-Vary` w `vcl_hash`), i ile
wynosi `grace`. **Nie przełączaj** `caching_application` na Varnish — bez
działającego Varnisha zepsujesz sklep.

**Kryteria odbioru:**
- Wyjaśnisz, dlaczego `cacheable="false"` na jednym bloku wyłącza cache
  całej strony i czemu w praktyce się go unika.
- Wyjaśnisz, dlaczego samo `$_cacheTag` na modelu nie wystarcza (klasa
  musi implementować `IdentityInterface`, bo `Tag\Strategy\Identifier`
  zwraca `[]` dla innych obiektów) i po co dwa poziomy tagów (per-wpis i
  listy).
- Wyjaśnisz, dlaczego surowy SQL nie unieważnia FPC i co to oznacza dla
  importów hurtowych (po nich potrzebny jest ręczny `cache:clean`).
- Scenariusze z punktu C.3 dają dokładnie opisane wyniki na Twoim kodzie.

---

## Etap 15 — Stores / Websites / Store Views (dodatek pod certyfikację AD0-E724)

**Cel:** zrozumieć hierarchię Website → Store (Group) → Store View i
rozwiązywanie configu po scope (`default` → `website` → `store`) — temat
pokrywany w sekcji "Architecture" egzaminu Adobe Commerce Developer
Professional, dotąd niepokryty w tym planie (cały plan do tej pory działał
na jednym store view — patrz
`docs/adobe-commerce-developer-professional-gap-analysis.md`).

**Kluczowe pojęcia:** Website (poziom domeny/checkout), Store Group (root
category, "sklep" logiczny), Store View (locale/waluta/UI), `core_config_data`
(kolumny `scope`/`scope_id`), `ScopeConfigInterface::getValue()` z rozpoznawaniem
bieżącego store'a bez podawania kodu, `catalog/price/scope` (global vs
per-website ceny), przełącznik `?___store=<code>`.

**Środowisko (stan wyjściowy, sprzed ćwiczenia):** repo miało **jeden**
website (`base`) i **jeden** store view (`default`) — zweryfikowane `bin/magento store:website:list` /
`bin/magento store:list`. `bin/n98-magerun2` ma gotowe komendy CLI do
tworzenia struktury bez klikania w adminie: `sys:website:create`,
`sys:store-group:create`, `sys:store:create` (odpowiednio `*:delete`, gdyby
trzeba było posprzątać).

**Szkielet już wygenerowany** (`Training_Greeting` + `Training_HelloWorld`):
- `Training_Greeting/etc/adminhtml/system.xml` — nowa grupa "Storefront" z
  polem `greeting_suffix` (scope `store`, czyli `showInStore="1"`).
- `Training_Greeting/etc/config.xml` — wartość domyślna `(default scope)`.
- `Training_HelloWorld/Block/Greeting.php` — wstrzyknięty
  `ScopeConfigInterface`, nowa metoda `getGreetingSuffix(): string` z `// TODO`
  (docblock zadaje pytanie, na które warto odpowiedzieć **przed** napisaniem
  kodu — patrz punkt B.2 niżej).
- `view/frontend/templates/greeting.phtml` — doklejone wywołanie
  `getGreetingSuffix()` obok istniejącego `getGreeting()`.
- Zweryfikowane: `/helloworld` renderuje się (`200`, `MISS`), sufiks na razie
  pusty (`"Hello, Magento! "`) — szkielet nie psuje strony, tylko nic jeszcze
  nie dokleja.

**Zadanie (do Ciebie):**

**A. Diagnostyka struktury (bez kodu).**
1. `bin/magento store:website:list`, `bin/magento store:list` — potwierdź
   stan wyjściowy (1 website, 1 store view).
2. Zajrzyj do tabel `store_website`, `store_group`, `store` (`bin/mysql`) —
   zobacz kolumny `website_id`, `group_id`, `store_id` i jak się do siebie
   odnoszą (Store Group ma `root_category_id` i `website_id`; Store View ma
   `group_id`).
3. Zajrzyj do `core_config_data` (`SELECT * FROM core_config_data WHERE path
   LIKE 'training_greeting%'`) — na starcie powinien tam być tylko wpis(y)
   scope `default`. Zwróć uwagę na kolumny `scope`/`scope_id` — to one, nie
   nazwa tabeli, decydują o poziomie.

**B. Drugi Store View, ta sama Website (scope: `store`).**
1. Stwórz drugi store view na istniejącym website `base` przez
   `bin/n98-magerun2 sys:store:create <code> "<Nazwa>" --website-id=1`
   (użyje domyślnej grupy website `base`). `bin/magento cache:flush` po
   utworzeniu.
2. **Zanim napiszesz kod** w `getGreetingSuffix()`: przewiduj — jeśli
   ustawisz wartość pola "Greeting suffix" na poziomie **store view**
   (Stores > Configuration > Training Greeting > Storefront, ze
   scope-switchera w lewym górnym rogu przełączonego na Twój nowy store
   view), a wartość domyślna (scope `default`) zostanie inna — którą
   zobaczysz, wchodząc na `/helloworld?___store=<kod_nowego_store_view>`?
   Dopisz kod w `getGreetingSuffix()` (jedna linijka —
   `$this->scopeConfig->getValue(...)`) i sprawdź, czy przewidywanie było
   trafne.
3. Zweryfikuj w `core_config_data`: nowy wiersz powinien mieć
   `scope='stores'`, `scope_id=<id nowego store view>` — **nie**
   nadpisuje wiersza `default`, tylko go przesłania dla tego store view.

**C. Website-level scope.**
1. Stwórz drugą Website (`bin/n98-magerun2 sys:website:create <code>
   "<Nazwa>"`), na niej nową Store Group (`sys:store-group:create`) i na tej
   grupie kolejny store view (`sys:store:create --group-code=<code>`).
2. Ustaw wartość "Greeting suffix" na poziomie **website** (nie store view)
   dla nowej website. Sprawdź store view należący do tej website — dostaje
   wartość website, mimo że nie ma własnego wpisu na poziomie `store`. To
   jest właśnie fallback: `store` → `website` → `default`, sprawdzany w tej
   kolejności aż trafi na pierwszy istniejący wpis.
3. `bin/magento config:show training_greeting/storefront/greeting_suffix
   --scope=websites --scope-code=<code>` vs bez `--scope` — porównaj wynik.

**D. Ceny per scope (diagnostyka, bez kodu w Training).**
1. Sprawdź `bin/magento config:show catalog/price/scope` (domyślnie global —
   `0`). Przełącz na website (`bin/magento config:set catalog/price/scope
   1`), `bin/magento indexer:reindex` (ceny są indeksowane).
2. W adminie, na dowolnym produkcie, przełącz scope-switcher na Twoją drugą
   website i ustaw inną cenę niż globalna. Sprawdź przez
   `ProductRepositoryInterface::get($sku, false, $storeId)` (np. w
   `bin/magento's` `dev:console` albo szybkim skrypcie w `bin/cli`), że dwie
   różne wartości `$storeId` dają dwie różne ceny tego samego SKU.
3. Cofnij `catalog/price/scope` na `0` (global) po eksperymencie, chyba że
   świadomie chcesz zostawić multi-website pricing włączone —
   **przełączanie tego configu w realnym sklepie z danymi cenowymi jest
   nieodwracalne bez ręcznej migracji danych**, więc rozumienie tego
   ostrzeżenia jest częścią zadania.

**E. Stretch:** przeczytaj `Magento\Store\Model\StoreManager::getStore()` —
skąd bierze bieżący store, gdy nie podasz argumentu (kolejność: parametr
requestu `___store` → cookie → store domyślny website'u z current group).
Sprawdź nagłówki na `/helloworld?___store=<kod>` przy pierwszym i drugim
requeście (bez parametru) — czy druga wizyta "pamięta" wybrany store view, i
po czym (podpowiedź: cookie `store`, plus jak to się ma do `X-Magento-Vary` z
Etapu 14).

**Status (2026-09-21):** A–D wykonane i zmierzone, do zrobienia zostaje
stretch E.
- ✅ B: `mystore` na `base`; wiersz `stores`/2 w `core_config_data`.
- ✅ C: `my_website` → `my_group` → `my_store_my_website`; wiersz `websites`/2;
  store 3 dziedziczy wartość website (brak własnego wiersza).
- ✅ D: `catalog/price/scope` = 1, wariant 1797 kosztuje 34 na `base` i 30 na
  `my_website` (baza, indeks cen i `ProductRepository` w kontekście store'a).
- ✅ D.3: `catalog/price/scope` cofnięte na `0` (`config:set` sam oznaczył indeks
  cen jako `Reindex required`, po `indexer:reindex catalog_product_price` i
  `cache:flush` wszystkie trzy store views dają dla 1797 cenę 34). Zmierzone
  skutki: wiersz `store_id=3` z ceną 30 **zostaje w bazie**
  (`catalog_product_entity_decimal`), ale jest ignorowany, a indeks dla website 2
  wraca do 34. Ponowne przełączenie na `1` przywróciłoby 30 bez ponownego
  wpisywania. Na sklepie z prawdziwymi danymi to właśnie ta rozbieżność między
  zapisanymi a obowiązującymi cenami jest ryzykiem.
- ⏳ E (stretch): nie zrobiony.

**Zmierzone `/helloworld` po `cache:clean full_page`:**
`(default scope)` bez parametru i dla `?___store=default`, `changed to mystore`
dla `mystore`, `my_website` dla `my_store_my_website`.

**Ustalenia z ćwiczenia (warte zapamiętania):**
- **Nieaktywny store view po cichu wraca do domyślnego.** `StoreResolver`
  łapie `NoSuchEntityException` z `getActiveStoreByCode()`, czyści parametr i
  cookie `store` i bierze domyślny — bez błędu. Tak wyglądało `?___store=mystore`
  przy `is_active=0`.
- **`___store` a website.** `StoreResolver/Website::getAllowedStoreIds()` ma
  warunek `($scopeCode && ta sama website) || (!$scopeCode)`. Bez `MAGE_RUN_CODE`
  (jak tu) `$scopeCode` jest pusty, więc `?___store=` przełącza na **dowolny
  aktywny** store view, także z innej website (zmierzone: `my_store_my_website`
  działa pod `magento.test`). Z ustawionym `MAGE_RUN_CODE` (osobne domeny per
  website) działałoby tylko w obrębie tej website — tego nie sprawdzano.
- **Formularz admina ustawia `website_id` store view z jego grupy**
  (`Backend/.../Store/Save.php:113`). Store view wpisany SQL-em może mieć
  niespójne `website_id` (u nas `0`), którego formularz nigdy by nie zapisał.
- **Website w scope-switcherze pojawia się tylko, gdy ma grupę ze store view**
  (szablon rysuje ją w pętli po store views), a website i grupa są w nim
  etykietami — klikalne są wyłącznie store views.
- **Switcher na stronie produktu pokazuje tylko website, do których produkt
  jest przypisany** (`Product/Edit.php:90`: `setWebsiteIds($product->getWebsiteIds())`).
  Bez przypisania do `My Website` nie da się tam ustawić ceny per website.
- **`config:show` bez `--scope` nie pokazuje wartości z `config.xml`**
  (zwraca puste); do sprawdzania defaultów użyj `ScopeConfigInterface`.
- **FPC trzyma osobny wpis dla każdego URL-a z query stringiem**, a zapis
  configu w adminie go nie odświeża — po zmianie wartości trzeba
  `cache:clean full_page` (zob. Etap 14, A.3).
- Storefront drugiej website nie pokaże jeszcze ceny: jedyny produkt w
  `my_website` to niewidoczny wariant 1797, a jego konfigurowalny rodzic (1812)
  nie jest do niej przypisany.

**Kryteria odbioru:**
- ✅ (dane utworzone) Umiesz narysować/opisać hierarchię Website → Store Group → Store View na
  Twoich własnych, utworzonych w tym etapie encjach (nie tylko na
  `base`/`default`).
- ✅ (zmierzone) Wyjaśnisz kolejność fallbacku configu (`store` → `website` → `default`) i
  pokażesz to na realnym wierszu w `core_config_data`, który sam(a)
  utworzyłeś/aś.
- ✅ `getGreetingSuffix()` daje różne wyniki na `/helloworld` w zależności od
  `?___store=`, zgodnie z Twoim przewidywaniem z punktu B.2 (albo: potrafisz
  wyjaśnić, dlaczego przewidywanie było błędne).
- Wyjaśnisz różnicę między `catalog/price/scope` = global a website, i
  dlaczego przełączenie tego na produkcyjnym sklepie z istniejącymi danymi
  jest ryzykowną, jednokierunkową operacją.

---

## Jak korzystać z tego planu z Claude Code

- Rób jeden checkbox/etap na raz, commituj po zamknięciu etapu.
- Proś o **code review** konkretnego pliku zamiast "sprawdź czy działa" — szybciej
  złapiesz błędy koncepcyjne.
- Jeśli utkniesz na >30 min na jednym problemie — powiedz na czym dokładnie utknąłeś
  (błąd, log, co próbowałeś) zamiast prosić o gotowe rozwiązanie całego zadania.
- Zobacz też `CLAUDE.md` w root repo — tam są komendy i konwencje specyficzne dla
  tego środowiska.
