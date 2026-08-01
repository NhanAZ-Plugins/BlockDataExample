# BlockDataExample

A small PocketMine-MP plugin showing how to use the [BlockData virion](https://github.com/NhanAZ-Libraries/BlockData) to attach persistent information to blocks.

This is a demonstration plugin, not a complete protection system.

## What it demonstrates

- Saving the player name and placement time when a block is placed.
- Allowing only the owner or a player with bypass permission to break that block.
- Toggling inspection mode with `/inspect` and viewing stored block information.
- Building one standalone PHAR with BlockData shaded by DevTools.

## Download a PHAR

Open the repository's [Actions page](https://github.com/NhanAZ-Plugins/BlockDataExample/actions/workflows/build.yml), select a successful run, and download its artifact. Extract the ZIP and copy `BlockDataExample.phar` to the production server's `plugins/` directory.

The PHAR already contains a private shaded copy of BlockData. A production server does not need DevTools or a separate BlockData installation.

## Build on every commit

The workflow at `.github/workflows/build.yml`:

1. Checks out an exact BlockData commit into `virions/BlockData`.
2. Sets up PocketMine PHP through the Node.js 24 compatible path.
3. Runs `NhanAZ-Plugins/DevTools@v0.1.0`.
4. Verifies the shaded BlockData classes and LGPL license inside the PHAR.
5. Uploads exactly one downloadable artifact for 14 days.

PHPStan is off because the workflow intentionally omits the `phpstan` input.

The dependency declaration is kept in `devtools.yml`:

```yaml
virions:
  - name: BlockData
    version: ^1.0.0
```

## Local development with DevTools

Install the DevTools release PHAR and use this layout:

```text
server/
|- plugins/
|  |- DevTools.phar
|  `- BlockDataExample/
|- virions/
|  `- BlockData/
`- build/
```

Restart the server, then run:

```text
/devtools virions
/devtools doctor BlockDataExample
/devtools build BlockDataExample
```

The result is `build/BlockDataExample.phar`.

## Usage

Place a block to save its owner and placement time. Run `/inspect`, then right-click a block to display its saved data. Run `/inspect` again to disable inspection mode.

Only the recorded owner may break a tracked block unless the player has `blockdata.bypass`.

## Permissions

| Permission | Default | Purpose |
| --- | --- | --- |
| `blockdata.command.inspect` | Everyone | Use `/inspect`. |
| `blockdata.bypass` | Operator | Break blocks owned by another player. |

## Source integration

The complete integration is in `src/BlockDataExample/Main.php`. The essential setup is:

```php
use NhanAZ\BlockData\BlockData;

protected function onEnable() : void{
    $this->blockData = BlockData::create($this, autoCleanup: false);
}
```

See the [BlockData documentation](https://github.com/NhanAZ-Libraries/BlockData) for the complete API.
