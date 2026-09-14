# Poukazy

Malá aplikace pro správu dárkových poukazů: administrace pro vydávání poukazů a JSON API pro partnery (pokladní systémy). Nette 3.2, PHP 8.2, MySQL 8.

## Spuštění

```bash
docker compose up -d --build
cp config/local.neon.dist config/local.neon
docker compose exec app composer install
docker compose exec app php bin/migrate.php
docker compose exec app php bin/migrate.php --test
```

Aplikace běží na http://localhost:8080. Kontejner `app` běží pod UID 1000; pokud máte jiné, spusťte `UID=$(id -u) GID=$(id -g) docker compose up -d`.

| Uživatel | Heslo | Role |
| --- | --- | --- |
| admin@example.com | heslo123 | administrátor |
| alfa@example.com | heslo123 | partner Trafika Alfa |
| beta@example.com | heslo123 | partner Pokladní síť Beta |

API klíče partnerů: Trafika Alfa `alfa-test-key-123`, Pokladní síť Beta `beta-test-key-456`. Databáze ukládá jejich SHA-256 hash.

```bash
curl http://localhost:8080/api/v1/ping
```

## Užitečné příkazy

```bash
docker compose exec app composer test                   # testy (Nette Tester)
docker compose exec app php bin/migrate.php             # spustí nové migrace
docker compose exec app php bin/migrate.php --fresh     # smaže databázi a nahraje vše znovu
docker compose exec db mysql -upoukazy -ppoukazy poukazy
```

Demo data jsou relativní k dnešnímu datu, `--fresh` je obnoví.

## Struktura

- `app/Model` doménová logika (poukazy, přihlašování)
- `app/Presentation` presentery a Latte šablony, každý presenter má svou složku
- `migrations` číslované SQL migrace, spouští je `bin/migrate.php`
- `tests` testy v Nette Testeru; testy s databází používají `poukazy_test`

## Konvence

- Částky jsou vždy v haléřích jako `int`.
- Změna schématu = nový soubor v `migrations/` (např. `003_popis.sql`). Existující migrace se nemění.
- Služby s názvem `*Facade`, `*Repository`, `*Generator` a `*Authenticator` se registrují do DI automaticky (`config/services.neon`).
- Aktuální čas se bere ze služby `App\Model\Clock`, aby šel v testech podvrhnout.
