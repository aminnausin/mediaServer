# Version Management & Release Workflow

This project uses a semantic versioning strategy with automated releases through GitHub Actions.

## Branch Strategy

### `main` - Beta Development Branch

- Always holds the **next minor version** as `X.Y.0-beta.N`
- Never holds patch versions (always `X.Y.0`)
- Each push increments the beta counter: `0.16.0-beta.1`, `0.16.0-beta.2`, etc.
- To bump the base version (minor or major), use the manual workflow dispatch
- Merging a PR to prod will bump the base version automatically (minor)

### `prod` - Production Branch

- Holds stable production versions: `X.Y.Z`
- Direct pushes are disabled
- Takes version from file merged into the branch (no bumping happens here)
- Releases are triggered by merging PRs from `main` or `release/*`
- Automatically generates changelogs from conventional commits

### `release/*` - Patch Release Branches (WIP)

- Created from `prod` or cherry-picked from `main` for patch fixes
- Each push increments version and RC counter: `0.16.1-rc.1`, `0.16.1-rc.2`, etc.
- Format: `release/0.16.x` (the `x` is just notation, should be used in automation somehow)
- Merges to `prod` as patch releases

## Release Types

### Minor Release (from `main` to `prod`)

```bash
# Current state
prod:  0.15.2
main:  0.16.0-beta.5

# After PR from main → prod
prod:  0.16.0  # Takes version from main
main:  0.17.0-beta.0  # Continues with beta releases from next minor version
```

**Steps:**

1. Develop features on `main` → auto-tags as `0.16.0-beta.N`
2. When ready, create PR from `main` to `prod`
3. ~~PR validation checks version bump is valid~~
4. Merge PR → auto-releases `0.16.0` with changelog
5. Main is bumped to `0.17.0-beta.0`

### Major Release (from `main` to `prod`)

```bash
# First, bump major version on main
# Run: Actions → "CI & Docker Release (Beta)" → Run workflow → Select "major"

# Before
main:  0.16.0-beta.5
# After workflow dispatch
main:  1.0.0-beta.0

# Continue development
main:  1.0.0-beta.1, 1.0.0-beta.2, ...

# Create PR main → prod when ready
prod:  1.0.0
```

### Patch Release (from `release/*` to `prod`)

`WIP`

## Conventional Commits

All commits should follow the [Conventional Commits](https://www.conventionalcommits.org/) format for automatic changelog generation:

### Format

```md
<type>[optional scope]: <description>

[optional body]
```

### Types

- **feat**: New feature or addition
- **fix**: Bug fix
- **docs**: Documentation changes
- **style**: Code style changes (linting, etc.)
- **refactor**: Code refactoring
- **perf**: Performance improvements
- **test**: Adding or updating tests
- **chore**: Maintenance tasks
- **build**: Build system changes
- **ci**: CI configuration changes
- **revert**: Reverting previous commits

### Breaking Changes

```md
feat!: redesign user authentication API

BREAKING CHANGE: The authentication endpoint has changed from /auth to /api/v2/auth
```

### Examples

```bash
feat: add dark mode support
feat(ui): implement responsive player controls
fix: memory leak in open graph image generator
fix(api): folder name overwritten by title
docs: update readme
perf: eager load users on tasks
refactor(frontend): simplify records api handling
```

## Changelog Generation

Changelogs are automatically generated from conventional commits when merging to `prod`:

- **Features** (`feat:`) → ✨ Features section
- **Bug Fixes** (`fix:`) → 🐛 Bug Fixes section
- **Performance** (`perf:`) → ⚡ Performance Improvements section
- **Breaking Changes** (`!:` or `BREAKING CHANGE`) → ⚠️ BREAKING CHANGES section
- **Refactoring** (`refactor:`) → ♻️ Code Refactoring section
- **Documentation** (`docs:`) → 📝 Documentation section

The changelog is:

1. Appended to `CHANGELOG.md` in the repo
2. Used as the GitHub Release description
3. Generated from commits between previous and current release tags

## Workflows

### 1. `ci-main.yml` - Beta Releases

**Triggers:**

- Push to `main` (excludes VERSION and CHANGELOG.md changes)
- Manual workflow dispatch (to bump major/minor version)

**Actions:**

- Runs tests
- Increments beta counter (or bumps version if manual)
- Creates git tag (e.g., `0.16.0-beta.5`)
- Builds and pushes Docker image with `beta` and version tags
- Creates GitHub pre-release with files if Docker files changed

### 2. `ci-prod.yml` - Production Releases

**Triggers:**

- PR merged to `prod` branch

**Actions:**

- Runs tests
- Creates production git tag (e.g., `0.16.0`)
- Generates changelog from conventional commits
- Updates `CHANGELOG.md`
- Builds and pushes Docker image with `latest` and version tags
- Creates GitHub release with changelog and Docker setup packages

### 3. `release-patch.yml` - Release Candidate (Patch)

**Triggers:**

- Push to `release/*` branches

**Actions:**

- WIP

## Version File

The `VERSION` file in the repository root contains the base semantic version:

- On `main`: `X.Y.0` (no beta suffix in file)
- On `prod`: `X.Y.Z` (production version)
- On `release/*`: `X.Y.Z` (patch version)

Git tags include the full version with suffixes:

- Beta: `X.Y.0-beta.N`
- RC: `X.Y.Z-rc.N`
- Production: `X.Y.Z`

## Docker Tags

### From `main` (Beta)

- `aminnausin/mediaserver:beta` (always latest beta)
- `aminnausin/mediaserver:0.16.0-beta.5` (specific beta)

### From `prod` (Production)

- `aminnausin/mediaserver:latest` (always latest stable)
- `aminnausin/mediaserver:0.16.0` (specific version)

### From `release/*` (RC)

- No Docker images built for RCs
- Docker images only built when merged to prod

## GitHub Releases

### Beta Releases (from `main`)

- Pre-release
- Includes Docker setup packages (if Docker files changed)
- Created automatically on push to main

### Production Releases (from `prod`)

- Includes:
  - Auto-generated changelog from conventional commits
  - Docker setup packages (Linux and Windows)
- Created automatically on merge to prod

### Release Candidates (from `release/*`)

- No GitHub releases for RCs
- Only git tags created
