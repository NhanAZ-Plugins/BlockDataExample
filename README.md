# BlockDataExample

BlockDataExample is an Axolotl-PM plugin that demonstrates persistent block ownership with the BlockData virion.

[![Build](https://github.com/NhanAZ-Plugins/BlockDataExample/actions/workflows/build.yml/badge.svg)](https://github.com/NhanAZ-Plugins/BlockDataExample/actions/workflows/build.yml)

## Overview

The plugin records a block's owner and placement time, restricts breaking owned blocks, and lets players inspect stored data. It is a working example of the [BlockData library](https://github.com/NhanAZ-Libraries/BlockData), not a complete protection system.

## Features

- Save ownership data when a player places a block.
- Allow the owner and players with bypass permission to break a recorded block.
- Inspect ownership and placement time with `/inspect`.
- Package a private shaded copy of BlockData into one PHAR.

## Requirements and compatibility

- Axolotl-PM 5.49.1 is the pinned CI and server smoke target. The plugin manifest declares the 5 API family.
- PHP 8.1 or newer with the extensions required by Axolotl-PM, including LevelDB and JSON.
- [BlockData 1.0.1](https://github.com/NhanAZ-Libraries/BlockData/releases/tag/v1.0.1) is pinned in the build workflow. The production PHAR contains the library.

Earlier Axolotl-PM 5.x releases and in-game behavior without a Minecraft client have not been independently verified.

## Installation

Download `BlockDataExample.phar` and `SHA256SUMS.txt` from the [v1.0.1 release](https://github.com/NhanAZ-Plugins/BlockDataExample/releases/tag/v1.0.1). Copy the PHAR into the server's `plugins/` directory and restart the server. DevTools and a separate BlockData installation are not needed on the production server.

The release includes `devtools-build.json` and the exact PHAR SHA-256. Compare the hash before deployment. The same build is available from its successful [workflow run](https://github.com/NhanAZ-Plugins/BlockDataExample/actions/runs/37992281547).

## Usage

When a player places a block, the plugin stores the player's name and a Unix timestamp. Only that player or someone with `blockdata.bypass` may break the recorded block. A break by the owner removes its data.

Run `/inspect`, then right-click a block to view its stored owner and placement time. Run `/inspect` again to turn inspection off. The command is available to players with `blockdata.command.inspect`.

This example expects ownership records with a string `owner` and integer `placed_at`. Invalid records are reported during inspection. An invalid record is removed when its block is broken.

## Commands and permissions

| Name | Default | Purpose |
| --- | --- | --- |
| `/inspect` | Everyone | Toggle inspection mode. |
| `blockdata.command.inspect` | Everyone | Allow use of `/inspect`. |
| `blockdata.bypass` | Operator | Allow breaking another player's recorded block. |

There is no configuration file. The plugin stores data under its data folder through BlockData.

## Building with DevTools

The [workflow](.github/workflows/build.yml) checks out BlockData at a fixed source commit, validates its manifest, runs PHPStan level max against pinned Axolotl-PM source, and builds with [DevTools 1.0.1](https://github.com/NhanAZ/DevTools/releases/tag/v1.0.1). A verification script checks the PHAR manifest, shaded classes, and both license texts before upload. It treats LF and CRLF as equivalent when comparing legal text across operating systems.

For local folder development, place the official DevTools PHAR in the server's `plugins/` directory. Put BlockData source in `virions/BlockData` next to this project. Then run:

```text
/devtools doctor BlockDataExample
/devtools build BlockDataExample
```

The result is `build/BlockDataExample.phar`. The source dependency declaration is in [`devtools.yml`](devtools.yml).

## Development and testing

The PHP source and verifier can be checked locally with:

```sh
php -l src/BlockDataExample/Main.php
php -l tools/verify-build.php
php tools/verify-build.php build/BlockDataExample.phar virions/BlockData
```

The build workflow additionally runs PHPStan level max and validates the PHAR. A clean server boot checks that the built plugin loads, while block placement and inspection still require an in-game test. See the [changelog](CHANGELOG.md) for update and rollback notes.

## License, credits, and support

Copyright 2023-2026 NhanAZ. BlockDataExample 1.0.1 is licensed under [AGPL-3.0-only](LICENSE). Earlier source revisions carried GPL-3.0 license text and remain available in repository history. The bundled BlockData virion retains its separate [LGPL-3.0-or-later license](https://github.com/NhanAZ-Libraries/BlockData/blob/master/LICENSE), which the PHAR includes.

Report defects in [GitHub Issues](https://github.com/NhanAZ-Plugins/BlockDataExample/issues). Community support is available through [NhanAZ Discord](https://discord.gg/j2X83ujT6c).
