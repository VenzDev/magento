# CLAUDE.md

Ten plik daje Claude Code kontekst potrzebny do pracy w tym repozytorium.

## Czym jest to repo

Środowisko developerskie do **nauki Magento 2**, oparte o Mark Shust's
`docker-magento`. Magento (2.4.9 CE) jest zainstalowane w `src/`, ale `src/` jest
w większości **gitignored** — jedynym śledzonym fragmentem jest `src/app/code/`
(patrz `.gitignore`). To znaczy: cały core, `vendor/`, `generated/`, `var/` itd. nie
są w git, tylko własne moduły.

Plan nauki (etapy, zadania, kryteria odbioru) jest w
[`docs/plan-nauki-magento.md`](docs/plan-nauki-magento.md) — czytaj go razem z tym
plikiem, kiedy pomagasz z zadaniami.

## Struktura repo

- `bin/` — skrypty wrapper do Docker-compose (odpowiedniki `docker compose exec ...`
  na sterydach). Preferowane wejście zamiast gołego `docker compose`.
- `Makefile` — cienka nakładka na `bin/*` (`make start` == `bin/start`). Oba działają
  identycznie, `bin/` jest bardziej bezpośrednie.
- `compose*.yaml` — definicje kontenerów (php-fpm, nginx, db, redis, opensearch,
  rabbitmq, mailcatcher). `compose.versions.yaml` jest generowany, nie edytuj ręcznie
  (patrz `lib/versions.sh`).
- `env/*.env` — konfiguracja per-serwis (dane logowania do bazy, RabbitMQ, itd.).
- `src/` — instalacja Magento. **Jedyne co edytować pod git**: `src/app/code/`.
  Reszta (`vendor/`, `pub/static`, `generated/`, `var/`) to build/runtime output.
- `template/` — szablony konfiguracyjne (np. nginx) kopiowane do `src/` przy setupie.

## Konwencje projektu

- Własne moduły: `src/app/code/Training/<NazwaModulu>`, vendor namespace **`Training`**.
- Frontend to domyślna, natywna **Luma** — brak zainstalowanej Hyvä
  (`composer.json` nie ma `hyva-themes/*`, `app/design/frontend/` ma tylko
  pakietowe motywy `Magento/*`). Etap 9 planu nauki i wszystkie zadania
  frontendowe zakładają Lumę, chyba że jawnie zainstalujesz Hyvä jako osobny krok.
- Deklaratywny schemat (`etc/db_schema.xml`) i deklaratywne data patch
  (`Setup/Patch/Data/*`) — **nie** stare `InstallSchema`/`UpgradeSchema` (Magento 2.4.x
  standard).
- Nowe atrybuty/kategorie/produkty tworzone w Setup Patch wymagają
  `appState->emulateAreaCode('adminhtml', ...)` w kontekście CLI.
- Nowa kategoria przez `CategoryRepository`: `setPath($parent->getPath() . '/')`
  **z końcowym slashem** — bez niego resource model nie dopisuje nowego ID i usunięcie
  kategorii kaskadowo kasuje rodzica.
- `ProductRepositoryInterface::getList()` domyślnie zwraca tylko produkty
  in-stock i zaindeksowane — jeśli produkt "znika" z wyników, sprawdź to najpierw
  zanim zaczniesz debugować SearchCriteria.

## Częste komendy

```bash
make start / bin/start          # uruchom kontenery
make stop / bin/stop            # zatrzymaj kontenery
make status / bin/status        # status kontenerów
bin/magento <cmd>                # Magento CLI (w kontenerze)
bin/n98-magerun2 <cmd>            # magerun2 CLI
bin/cli <cmd>                    # dowolna komenda w kontenerze phpfpm (z TTY)
bin/composer <cmd>               # composer w kontenerze
bin/mysql                        # MySQL CLI z danymi z env/db.env
bin/log [plik]                   # tail logów Magento
bin/dev-test-run <typ>           # uruchom PHPUnit dla danego typu testów (np. unit, integration)
bin/setup-integration-tests      # jednorazowy setup bazy pod testy integracyjne
bin/phpcs / bin/phpcbf           # code style (Magento2 ruleset)
bin/analyse <ścieżka>             # PHPStan
```

Po zmianie w `src/app/code/`: zwykle trzeba
`bin/magento setup:upgrade && bin/magento cache:flush`, a przy zmianach schematu/UI
komponentów dodatkowo `bin/magento indexer:reindex`.

## RabbitMQ (integracja z PIM — Etap 7 planu nauki)

- Kontener `rabbitmq` (obraz `markoshust/magento-rabbitmq`), już podpięty do
  Magento przez `queue` w `src/app/etc/env.php`.
- Management UI: http://localhost:15672 — dane logowania w `env/rabbitmq.env`
  (domyślnie `magento` / `magento`, vhost `/`).
- Kluczowe komendy: `bin/magento queue:consumers:list`,
  `bin/magento queue:consumers:start <consumer_name>`.
- Pliki konfiguracyjne konsumenta rozproszone są w module: `etc/communication.xml`
  (kontrakt/topic), `etc/queue_topology.xml` (exchange + binding),
  `etc/queue_consumer.xml` (konsument + kolejka), `etc/queue_publisher.xml` (opcjonalnie,
  jeśli chcesz nadpisać domyślny connector publishera).

## Jak pracować nad zadaniami z planu nauki

- To repo istnieje głównie do nauki — **nie dawaj od razu gotowych, kompletnych
  rozwiązań zadań z `docs/plan-nauki-magento.md`**, chyba że użytkownik jawnie o to
  poprosi. Domyślnie: podpowiedzi, review konkretnego pliku, wyjaśnienie koncepcji,
  wskazanie błędu — implementację robi użytkownik.
- Gdy użytkownik prosi o review/debug konkretnego pliku modułu `Training/*` —
  potraktuj to normalnie jak każde zadanie inżynierskie (czytaj, testuj, popraw).
- Po zamknięciu etapu z planu, jeśli użytkownik prosi o commit — commituj tylko
  `src/app/code/Training/**` i pliki poza `src/` (reszta `src/` jest gitignored i tak
  nie trafi do repo).
