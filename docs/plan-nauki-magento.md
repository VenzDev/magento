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

**Cel:** zrozumieć model EAV, indeksowanie i pułapki `ProductRepositoryInterface`
(patrz też Twoja notatka w pamięci: `getList` domyślnie filtruje po stanie
magazynowym/indeksie).

**Zadanie:**
- Napisz `Setup\Patch\Data` (declarative data patch), który tworzy nowy atrybut
  produktu (np. `training_badge`, typ select z opcjami "Nowość"/"Promocja"/"Hit"),
  dodaje go do domyślnego zestawu atrybutów i czyni go filtrowalnym w warstwie
  nawigacji.
- Komenda CLI `bin/magento training:products:tag`, która przez
  `ProductRepositoryInterface` + `SearchCriteriaBuilder` znajduje produkty spełniające
  warunek (np. cena > X) i ustawia im atrybut `training_badge`.
- Sprawdź świadomie: czy Twoja komenda widzi produkt, który jest `disabled` albo ma
  `qty=0`? Dlaczego (albo dlaczego nie)?

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

Ten projekt nie ma jeszcze zdalnego repo na GitHubie — jeśli chcesz faktycznie
zobaczyć workflow w akcji (a nie tylko sprawdzić składnię YAML), będziesz musiał(a)
najpierw założyć repo na GitHubie i wypchnąć do niego ten kod (bez `src/`, zgodnie z
`.gitignore`).

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

## Jak korzystać z tego planu z Claude Code

- Rób jeden checkbox/etap na raz, commituj po zamknięciu etapu.
- Proś o **code review** konkretnego pliku zamiast "sprawdź czy działa" — szybciej
  złapiesz błędy koncepcyjne.
- Jeśli utkniesz na >30 min na jednym problemie — powiedz na czym dokładnie utknąłeś
  (błąd, log, co próbowałeś) zamiast prosić o gotowe rozwiązanie całego zadania.
- Zobacz też `CLAUDE.md` w root repo — tam są komendy i konwencje specyficzne dla
  tego środowiska.
