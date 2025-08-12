# Step-by-step: GitHub + WordPress.org setup

This covers **MailHealth Lite**, **RuleForge Lite**, **MailHealth Pro**, and **RuleForge Pro**.

---
## 1) Create the GitHub repos
Create four repos with these exact names:
- `mailhealth-lite` (public)
- `ruleforge-for-woo-lite` (public)
- `mailhealth` (private)
- `ruleforge-for-woo` (private)

Unzip each `*-repo.zip` and push the contents to its matching repo.

---
## 2) Configure repo settings
- Set description, website, and topics (see `CHECKLIST.md`).
- Default branch: `main`.
- Add branch protection (require PR review + PHPCS status).

For Lite repos, add **Secrets**:
- `WPORG_USERNAME`: your WordPress.org username
- `WPORG_PASSWORD`: your WordPress.org password

---
## 3) Install Composer locally (optional but recommended)
- macOS: `brew install composer`
- Windows: installer from getcomposer.org
- Linux: see getcomposer.org

Run once per repo:
```bash
composer install
```

---
## 4) Bump version before releasing
Run the script from repo root:
```bash
php tools/bump-version.php 0.9.1 mailhealth-lite
```
Adjust version and slug per plugin.

Commit the changes:
```bash
git add -A
git commit -m "Bump version to 0.9.1"
```

---
## 5) Tag a release
- **Lite** example:
```bash
git tag -a v0.9.1 -m "MailHealth Lite 0.9.1"
git push --tags
```
The unified workflow will:
1. Install PHPCS
2. Run coding standards
3. Build the plugin ZIP
4. Create a GitHub Release and attach the ZIP
5. (Lite only) Build SVN export and commit to WordPress.org

- **Pro** example:
```bash
git tag -a v0.2.3 -m "MailHealth Pro 0.2.3"
git push --tags
```
The unified workflow will test and build, then attach `*-pro.zip` to the Release.

---
## 6) Verify WordPress.org (Lite only)
- Plugin page updates within ~10–30 minutes.
- Banners/screenshots from `/assets` may take up to an hour.

---
## 7) Hotfix flow
1. Create a branch: `git checkout -b fix/xyz`
2. Commit fixes; open PR to `main`
3. Merge after PHPCS passes
4. Bump version + tag new release

---
## 8) Optional: Monorepo (not recommended)
If you must, put `/lite` and `/pro` in one repo and duplicate workflows per folder.
Separate repos are simpler for access control and releases.

---
## FAQ
**Q: PHPCS fails on warnings.**  
Edit `phpcs.xml.dist` and change some rules to `<severity>5</severity>` **or** run with `--warning-severity=0` (not recommended for long-term quality).

**Q: SVN commit failed auth.**  
Check `WPORG_USERNAME` / `WPORG_PASSWORD` secrets. Also ensure your repo name exactly matches the wp.org slug.

**Q: Where do WP.org banners/icons go?**  
In Git: `assets-wporg/`. The workflow copies them into SVN `/assets/` automatically.
