# Free exam pack (email capture)

A visitor leaves an email and gets, for free, the PAU Madrid exam statements (no solutions) of the last three years for Matemáticas II, Matemáticas CCSS, Física and Química. Then three follow-up emails offer the paid pack of the subject the visitor was looking at, and Premium.

The statements are already free on the website, so the free pack gives away nothing that is sold. The solutions stay in the paid packs.

## How it works

The signup is off until `LEAD_MAGNET_ENABLED=1` (default `0` in `.env`). When it is off, the landing page answers 404 and the boxes and the sitemap entry are hidden. Downloads, unsubscribe and the follow-ups keep working for people who already signed up.

- Landing page: `/examenes-pau-madrid-gratis`. It is also in the products sitemap.
- A signup box shows on Madrid PAU pages that have a pack: the exam page, the enhanced exam page, the subject listing and the file viewer of an exam. It is hidden from Premium users. It sends the code of the pack on that page, so the follow-ups offer that pack.
- Signup (`LeadSubscriptionManager::subscribe`): email plus a consent checkbox (not pre-ticked). A honeypot field and the `lead_magnet_subscribe` limiter (5 per IP per hour) stop bots. The same address gets at most one email every 10 minutes. The answer is always "check your email", so the form never tells whether an address is already stored.
- Double opt-in: the email holds a link to `/examenes-pau-madrid-gratis/descargas/{token}`. Opening it confirms the address and lists the PDFs. Each download redirects to a fresh presigned S3 URL, so the link in the email keeps working.
- Follow-ups: 2, 5 and 9 days after the confirmation (`LeadFollowUpSchedule`). Two follow-ups are never closer than one day. Only confirmed, subscribed addresses get them.
- Unsubscribe: every email has a link and `List-Unsubscribe` + `List-Unsubscribe-Post` headers (one-click, RFC 8058, required by Gmail and Yahoo). GET on `/emails/baja/{token}` shows a button, so mail scanners do not unsubscribe anybody; POST unsubscribes.
- A signup after an unsubscribe needs a new confirmation before any follow-up goes out.
- Admin: "Suscriptores del pack gratis" under Usuarios. It is read-only, with export. The consent date and IP are the proof of consent.

Code: `src/Service/LeadMagnet/`, `src/Controller/LeadMagnetController.php`, `src/Entity/LeadSubscriber.php`, `templates/views/lead_magnet/`, `templates/common/lead_magnet/`, `templates/email/lead_magnet/`.

## Before going live

### 1. Build the PDFs

`FreeExamPack::YEAR_RANGE` says which years the copy shows (now `2024-2026`). First check in production that the four subjects have statements for those years. If not, change the constant.

List the statement files with the DQL query of `docs/runbooks/create-pau-bundle.md` (section "Data audit"), filtered on `ty.year >= <first year>` and the four subject slugs (`matematicas`, `matematicas-cc-ss`, `fisica`, `quimica`). Keep only the statements, not the solutions; check the real file names first.

Download the source PDFs from S3 and merge one PDF per subject, ordered by year, exam weight and file weight, for example with `qpdf --empty --pages a.pdf b.pdf -- out.pdf`. Keep a manifest JSON of the source file ids next to the output, as for the paid packs. Do not commit the PDFs.

File names (they must match `FreeExamPack`):

```text
PAU-Madrid-Matematicas-II-enunciados-recientes.pdf
PAU-Madrid-Matematicas-CCSS-enunciados-recientes.pdf
PAU-Madrid-Fisica-enunciados-recientes.pdf
PAU-Madrid-Quimica-enunciados-recientes.pdf
```

### 2. Upload them to S3

```text
s3://<bucket>/lead-magnets/pau-madrid-enunciados-recientes/<file name>
```

The names stay the same every year, so a new year only means new PDFs and a new `YEAR_RANGE`.

### 3. Deploy and verify

Deploy with the wrapper (`cd /var/www && ./prepare_cda_coffe`); it runs the migration. Then:

```bash
cd /var/www/clasesdeapoyo
php bin/console app:lead-magnet:verify --env=prod
```

### 4. Turn it on

When `app:lead-magnet:verify` passes, set `LEAD_MAGNET_ENABLED=1` in `.env.local` on the server and clear the cache (or deploy again with the wrapper). Rebuild the sitemap so the landing page is listed.

### 5. Add the cron line

```cron
0 * * * * cd /var/www/clasesdeapoyo && php bin/console app:lead-magnet:send-follow-ups --env=prod >> var/log/lead-follow-ups.log 2>&1
```

`--limit` (default 50 per run) keeps the hourly runs under the daily cap of the Gmail account in `MAILER_DSN`. A mail error stops the run; the subscribers not reached stay due for the next run.

### 6. Smoke test

Sign up on the landing page with a real address, open the link, download one PDF, then use the unsubscribe link of the email.
