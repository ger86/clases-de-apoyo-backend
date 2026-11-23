# Free exam pack: production deployment (2026-11-23)

Feature: email capture with the recent PAU Madrid statements as the free gift. How it works and how to rebuild it: `docs/runbooks/free-exam-pack.md`.

## What was done

- Data audit over SSH (read-only DQL). The website is missing some years: no CCSS statements for 2022-2024, no Química for 2023-2024 or 2026. So each PDF holds the last three years that exist for its subject, and the file labels say which.
- The 32 source PDFs were read from `s3://clasesdeapoyosf/document/<providerReference>` with the server credentials (the local AWS user has no access to the production bucket), merged locally with `qpdf`, checked as statements (no solutions), and uploaded to `s3://clasesdeapoyosf/lead-magnets/pau-madrid-enunciados-recientes/`. Temporary files on the server were deleted.
- Deployed `9cd71d0` with `prepare_cda_coffe`. The migration `Version20261123152737` created `lead_subscriber`.
- `LEAD_MAGNET_ENABLED=1` added to the server `.env.local` (backup: `.env.local.bak-20261123-leadmagnet`), then `composer dump-env prod`, `cache:clear` and a php-fpm restart.
- `app:lead-magnet:verify` passes, `app:sitemap:build` lists the landing page, `app:lead-magnet:send-follow-ups` runs (0 emails, no subscribers yet).
- Hourly cron added on the server for `app:lead-magnet:send-follow-ups` (log: `var/log/lead-follow-ups.log`).
- Live checks: the landing page, a Madrid exam page, a Madrid subject listing and a Madrid exam file page answer 200 and show the signup box; the home page and `/packs` do not.

## Incident during the deploy

`prepare_cda_coffe` runs `cache:clear` before `composer dump-env prod`. The old `.env.local.php` did not have the new `LEAD_MAGNET_ENABLED`, so `cache:clear` failed ("Environment variable not found") and production kept serving the previous cache for a few minutes. Pages kept answering 200. Running `dump-env` and `cache:clear` again fixed it.

Any future change that adds an env var to `.env` hits the same problem: after the wrapper, run `composer dump-env prod` and `bin/console cache:clear --env=prod` again, or move `dump-env` before `cache:clear` in the wrapper.

## Open points

- A real signup test with a real inbox: confirm email, download, unsubscribe.
- The privacy policy (gdpr.direct legal hub) may need to cover marketing emails.
