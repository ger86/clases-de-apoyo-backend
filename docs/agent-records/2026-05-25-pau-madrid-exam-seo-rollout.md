# PAU Madrid Exam SEO Rollout Record

Date: 2026-05-25
Project: Clases de Apoyo backend
Status: deployed to production through commit `e336055`

## Purpose

This document records the SEO, AI-search, conversion, and mobile rendering work applied to Madrid PAU exam pages and related PAU packs. Future agents should use this as the historical implementation trace and `~/.agents/skills/enhance-exam-seo-page/SKILL.md` as the repeatable workflow for the next subjects or communities.

## Scope Completed

- Allowed AI/search crawlers in `public/robots.txt`.
- Built the first enhanced exam SEO page for Madrid Matemáticas II 2025 modelo.
- Generalized the enhanced exam rendering so only exams with complete catalog data use the new template.
- Applied the enhanced exam process to all Madrid Matemáticas II exams.
- Fixed the file access/rendering issue where some enunciados appeared as locked pack CTAs instead of visible enunciado links.
- Applied the enhanced exam process to Madrid Física exams.
- Updated the process after review showed some Física PDFs had Opción A and Opción B on separate pages; question summaries must cover both options when present.
- Applied the enhanced exam process to Madrid Química exams.
- Created and deployed the Madrid Matemáticas CCSS PAU pack.
- Fixed mobile horizontal overflow caused by enhanced exam tables.

## Main Commits

```text
fe00341 Allow AI search crawlers in robots
01d0633 Enhance Madrid math model SEO page
f91554e Generalize enhanced exam page rendering
30919d7 Enhance Madrid math exams from 2020
f3a3ab3 Enhance Madrid math exams from 2015
d2837d3 Enhance Madrid math exams from 2005
fc981d6 Enhance remaining Madrid math exams
8820f5b Refine Madrid math exam pack CTA
7e2c63f Fix pack exam enunciado visibility
57c223d Enhance Madrid physics exams from 2018
5c2c618 Enhance Madrid physics exams from 2009
9e82e5f Show both Madrid physics exam options
1a92b42 Finish Madrid physics exam enhancements
6d86c0e Enhance Madrid chemistry exams from 2015
ae5f73a Add Madrid math CCSS PAU pack
5954f63 Document Madrid math CCSS pack deployment
f5126c2 Enhance historic Madrid chemistry exams
e336055 Fix mobile overflow in enhanced exam tables
```

## Current Architecture

Enhanced exam pages are selected by `App\Service\Exam\EnhancedExamPageCatalog`.

The controller passes the catalog result to the exam template:

```text
src/Controller/KnowledgeTestController.php
templates/views/knowledge_tests/exam/exam.html.twig
```

The template chooses the enhanced rendering only when the catalog returns a complete record:

```text
templates/views/knowledge_tests/exam/seo/enhanced_exam.html.twig
```

Incomplete or missing catalog records intentionally fall back to the legacy exam page. This was chosen so future rollouts can be incremental by exam, subject, or community without exposing half-populated SEO pages.

The catalog validates required fields before enabling the enhanced template. Required content includes:

- page title and meta description
- summary paragraphs
- solution CTA
- statement/file labels
- exam data rows
- question summary rows
- topics
- practice steps
- related exams
- quick facts
- JSON-LD fields
- author information

## SEO And AI-Search Page Shape

The enhanced page adds clean HTML content that is useful to students, search engines, and AI agents:

- concise exam summary
- official enunciado link as visible HTML
- solution/pack CTA when the relevant PAU pack exists
- structured exam data
- question-by-question summary
- topic list
- practice steps
- related exam links
- quick facts sidebar
- canonical URL
- JSON-LD `BreadcrumbList`
- JSON-LD `Article` plus `LearningResource`

The implementation deliberately does not expose protected solution files. Solution CTAs route to the relevant PAU pack when a pack exists.

## Important Data Rule

For every new enhanced exam, extract or inspect the enunciado PDF before writing the summary. Do not summarize from only the first page if the PDF has multiple options, repertories, or alternative pages.

The Física rollout found that some exams had Opción A and Opción B on separate pages. The skill was updated so `questions` rows must represent every visible option/repertory when present.

## File Visibility Fix

The enhanced template now normalizes file names and treats names starting with `enunciado` as statement files. That prevents visible enunciados from being rendered as locked pack CTA links.

For locked files:

- solution files render the solution-specific pack CTA
- other locked files keep a file-specific pack label

This fixed pages such as:

```text
https://www.clasesdeapoyo.com/s/selectividad/madrid/matematicas/2022-junio-3
```

## Mobile Table Fix

Enhanced exam data and question summaries originally used full-width tables on every viewport. On mobile, the question table was too wide and created page-level horizontal scroll.

Commit `e336055` changed the enhanced template to render:

- mobile: stacked `dl`/section rows using `md:hidden`
- desktop/tablet: the original tables using `hidden md:table`

Local browser verification before deployment showed a 390px viewport with:

```json
{
  "innerWidth": 390,
  "documentScrollWidth": 390,
  "bodyScrollWidth": 390
}
```

Twig lint and asset build passed before deployment. Production was deployed through `/var/www/prepare_cda_coffe`; the wrapper fast-forwarded production to `e336055` and rebuilt assets successfully.

The live browser verification attempt after deployment was interrupted while trying to fetch Playwright CLI from npm. The deployment itself completed successfully.

## PAU Pack Work Connected To This Rollout

The enhanced pages are conversion-aware and route locked solution demand into subject-specific PAU packs when available.

Separate pack records exist for the major bundle work:

```text
docs/agent-records/2026-05-22-pau-madrid-math-pack.md
docs/agent-records/2026-05-22-pau-madrid-physics-pack.md
docs/agent-records/2026-05-23-pau-madrid-chemistry-pack.md
docs/agent-records/2026-05-25-pau-madrid-math-ccss-pack.md
```

The Matemáticas CCSS pack was created during this workstream for:

```text
https://www.clasesdeapoyo.com/s/selectividad/madrid/matematicas-cc-ss
```

Use those pack records for Stripe IDs, S3 details, sitemap notes, and product-specific verification.

## Repeatable Skill

The repeatable process is documented in:

```text
/Users/gerardofernandez/.agents/skills/enhance-exam-seo-page/SKILL.md
```

Key rules from the skill:

- Use `EnhancedExamPageCatalog`; do not add slug-specific Twig templates.
- Add a complete catalog record keyed by `{knowledgeTestSlug}/{communitySlug}/{subjectSlug}/{examSlug}`.
- Extract text from the enunciado PDF when content is needed.
- Check for multiple options/repertories/pages.
- Preserve protected solution access.
- Verify one enhanced page and one legacy fallback page.
- Use `.codex/code-reviewer.md` before pushing.
- Deploy with `/var/www/prepare_cda_coffe` only when requested.

## Verification Commands Used In This Workstream

Common local checks:

```bash
docker-compose exec -T php php bin/console lint:twig templates --no-interaction
docker-compose exec -T php php bin/console lint:container --no-interaction
docker-compose exec -T php composer stan
npm run build
git diff --check
```

For mobile layout debugging, use a real browser viewport and check that `document.documentElement.scrollWidth` does not exceed `window.innerWidth`.

## Production Deployment Convention

Production deployments for this repo should use:

```bash
ssh -o IdentitiesOnly=yes -o BatchMode=yes -i /Users/gerardofernandez/Projects/ClasesDeApoyo/clasesdeapoyo.pem ubuntu@35.180.205.41 'cd /var/www && ./prepare_cda_coffe'
```

After deployment, inspect:

```bash
cd /var/www/clasesdeapoyo
git status --short
```

If `config/reference.php` is modified by the wrapper, stash only that tracked generated file. Leave unrelated untracked server files untouched.

## Known Server State After Latest Deployment

After deploying `e336055`, `config/reference.php` was modified by the wrapper and was stashed with:

```text
post-deploy generated config reference
```

Unrelated untracked files remain on the server checkout, including `.env.save`, `.env.local.pricing-backup-*`, macOS `._*` files, and `public/.well-known/`. They were not touched because they are unrelated to this rollout.

## Next Suggested Work

- Continue enhanced page rollout by subject/community using the skill.
- Consider moving `EnhancedExamPageCatalog` data out of PHP if the catalog grows too large to maintain comfortably.
- Add automated coverage around `EnhancedExamPageCatalog::findForExam()` completeness behavior.
- Add a lightweight responsive regression check for enhanced exam pages.
