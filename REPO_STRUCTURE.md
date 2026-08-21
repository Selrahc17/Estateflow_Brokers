<!-- Repo structure overview for new developers -->
# Repository structure — estateflow_brokers

This file gives a concise, developer-friendly view of the repository layout and where to find the main pieces of the app.

## Top-level snapshot

```
estateflow_brokers/
├─ artisan
├─ composer.json
├─ package.json
├─ README.md
├─ vite.config.js
├─ public.chatbot.php
├─ app/
│  ├─ Http/
│  │  └─ Controllers/
│  └─ Models/
├─ bootstrap/
├─ config/
├─ database/
│  ├─ migrations/
  │  └─ seeders/
├─ public/
├─ resources/
│  ├─ css/
│  ├─ js/
│  └─ views/
├─ routes/
├─ storage/
├─ tests/
└─ vendor/
```

## Quick pointers

- **Root files:** [composer.json](composer.json), [package.json](package.json), [README.md](README.md), [vite.config.js](vite.config.js)
- **Entry point(s):** [public/index.php](public/index.php) (web), [public.chatbot.php](public.chatbot.php) (chatbot endpoint)
- **App logic:** [app](app/) — controllers live in [app/Http/Controllers](app/Http/Controllers/), Eloquent models in [app/Models](app/Models/)
- **Config:** [config](config/) — central place for env-driven settings
- **Database:** [database/migrations](database/migrations/) and [database/seeders](database/seeders/)
- **Public assets:** [public](public/) — compiled builds under [public/build](public/build/)
- **Front-end:** [resources/js](resources/js/) and [resources/css](resources/css/)
- **Routes:** [routes](routes/) — check `web.php` and `console.php`
- **Tests:** [tests](tests/) — `Feature` and `Unit` tests

## How to keep this file current

1. Manually edit this file when you add or reorganize major folders.
2. To generate a local tree output (Windows):

```powershell
tree /F /A > repo-tree.txt
```

On macOS/Linux you can run:

```bash
tree -a > repo-tree.txt
```

Then paste the relevant portion into this file.

## Suggested next steps for the team

- Add a short `CONTRIBUTING.md` with dev setup (env, composer/npm install, migrations, seeds, run server).
- Optionally add a script to auto-generate the tree into a `docs/` folder.

---
_Generated/edited by repository maintainer to help new developers quickly orient themselves._
