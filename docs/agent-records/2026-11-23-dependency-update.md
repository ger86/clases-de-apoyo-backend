# Dependency Update to Latest Versions

Date: 2026-11-23
Project: Clases de Apoyo backend
Status: implemented, verified, deployed to production on 2026-11-23.
Scope: PHP (`composer.json`), website front end (`package.json`) and the video tool (`tools/exam-video/package.json`).

## PHP

`composer update -W` moved 122 packages. `composer outdated --direct` is now empty and `composer audit` finds no advisories.

- **Symfony 8.0 to 8.1.** The blocker was `sonata-project/formatter-bundle` 6.x-dev, which requires the exact version `8.0` (see [2026-09-03-dependency-update.md](2026-09-03-dependency-update.md)). The stable 5.6.0 release allows `^8.1`, so the requirement is now `^5.6`. That also removed the four `config.audit.ignore` entries, because Symfony left v8.0.0.
- **doctrine/orm cap removed.** DBAL 4.6 has `Schema::edit()`, so `^3.8` with `doctrine/dbal ^4.6` works. `doctrine:schema:validate` passes and `doctrine:schema:update --dump-sql` reports nothing to change.
- **symfony/twig-bridge** was pinned to exactly `8.0.3` with no recorded reason. It now follows `8.1.*`.
- **stripe/stripe-php 19 to 23.** No code change needed. The SDK now pins API version `2026-11-18.endive` for the calls the app makes (Checkout Session create and retrieve, Billing Portal session, Subscription retrieve and cancel). Webhook payloads still follow the API version set on the Stripe webhook endpoint, not the SDK.
- Transitive majors: `doctrine/collections` 2 to 3, `knplabs/gaufrette` 0.11 to 1.0.

The markitup files listed in `config/packages/sonata_admin.yaml` (`bundles/sonataformatter/...`) do not exist. That is not new: 6.x-dev did not ship them either.

## Front end

All packages at their latest versions; `npm audit` is clean.

- **Node.js 22.18 or newer (or 24.11 or newer) is now required** by Encore 7 and Babel 8. This applies to the production server too, because `prepare_cda_coffe` builds the assets there.
- **Encore 4 to 7** is an ES module, so the config is now `webpack.config.mjs` with `import`/`export default`.
- **Babel 7 to 8** removed `useBuiltIns`/`corejs` from preset-env. The same "usage" polyfills now come from `babel-plugin-polyfill-corejs3`.
- **webpack-notifier removed** with `enableBuildNotifications()`. Version 1.17 makes the build fail when it cannot show a desktop notification (on this Mac its binary is x86 only, and a server has no desktop).
- **Tailwind 3 to 4.** `tailwind.config.js` is gone; the theme lives in `@theme` in `assets/app.css`, and PostCSS uses `@tailwindcss/postcss`. The official upgrade tool broke the stylesheet, so `app.css` was rewritten by hand from the v3 file. Points to keep in mind when editing it:
  - CSS outside a layer beats every utility in v4. The plain rules (`p` margin, `html` font size, font smoothing) live in `@layer base`, otherwise `<p class="mb-4">` loses its margin.
  - `.btn` and the `.btn-*` variants share one rule in `@layer components`. If `.btn` is a `@utility`, it beats `.btn-outline-primary` in `class="btn btn-outline-primary"` and the button turns white.
  - `.btn` sets a plain `line-height`, not `leading-5`, so `xl:text-lg` on a button can still change its line height as in v3.
  - Content sources are explicit (`source(none)` plus `templates/` and `assets/`), matching the old `content` setting.
- **webpack-dev-server** is now a direct devDependency, because Encore 7 no longer installs it. `npm run dev-server` still stops with "Don't enable versioning with the dev-server", but that is old: Encore 4 had the same rule with this config.
- **Browser targets changed.** Encore passes empty `targets` to Babel in both versions. Babel 7 then compiled for every browser; Babel 8 uses browserslist `defaults` (at the time of writing: iOS Safari 18.5 and newer, Chrome 109 and newer). The polyfill chunk went from 65 KB to 28 KB. Older phones get less transpiled code.
- Template class renames: `bg-gradient-to-r` to `bg-linear-to-r`, `break-words` to `wrap-break-word`, `rounded` to `rounded-sm`, `bg-opacity-50` to `bg-black/50`.

## Video tool

Remotion 4.0.380 to 4.0.546, React 19.3, KaTeX 0.19, TypeScript 7, all still pinned to exact versions. The new Remotion uses Chrome Headless Shell 158; the download from remotion.media kept dropping, so it was fetched with `curl -C -` and unpacked into `node_modules/.remotion/`.

## Verification

- `composer ci` green before and after: 87 tests, 197 assertions.
- Smoke harness (same method as the 2026-09-03 record, 97 checks: 25 crawled public pages, 8 API endpoints, every Sonata admin list, create and edit screen): identical status codes before and after.
- Stripe: webhook signature check and event parsing for the four handled event types work on SDK 23. **No live Stripe API call was made**, because the local `STRIPE_SECRET_KEY` is not a test key.
- Front end: 9 key pages rendered at 1280 px and 390 px with the old and the new CSS, then compared pixel by pixel. Identical, except the Google Maps iframe in the footer, which loads at random speed.
- Front-end JS: home, a file page and login run in headless Chrome with the built scripts and an error collector. Zero errors, and the cookie banner opens, so the scripts run. `encore dev` also builds.
- Video tool: `tsc --noEmit` passes, and the 18 review stills of `determinantes-ejercicio-17-filas-proporcionales` (normal and reel) are identical (largest pixel difference 2 of 255).

## Deployment

- Production Node is fine: `prepare_cda_coffe` loads nvm, whose default is Node 24.14. The system Node (`/usr/bin/node`) is still 18 and is not used by the build.
- `prepare_cda_coffe` never ran `npm install`. It now runs `npm ci` before `npm run build` (backup: `/var/www/prepare_cda_coffe.bak-20261123`).
- The server had a local, auto-generated edit to `config/reference.php` that would block `git pull`. It was discarded (diff saved in `/tmp/reference.php.server-20261123.diff`).
- **About 4 minutes of downtime (500 errors, 14:10 to 14:14 UTC).** `cache:clear` in the wrapper booted the old compiled container, which crashes with doctrine-bundle 3.4, so `cache:clear` and `doctrine:migrations:migrate` both failed. Fixed by deleting `var/cache/prod`, running `cache:warmup` and the migrations (already up to date), and restarting php8.4-fpm. A future major Doctrine or Symfony bump will hit the same problem with this wrapper.
- After the fix, the 10 checked public pages and API endpoints return the same status codes as before the deploy, and the new Tailwind 4 CSS is served.
