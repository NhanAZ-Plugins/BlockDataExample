# Changelog

## 1.0.1

- Build with DevTools 1.0.1 and the validated BlockData 1.0.1 virion source.
- Analyze the plugin at PHPStan level max against pinned Axolotl-PM 5.49.1 source.
- Handle malformed stored ownership records without PHP type errors.
- Skip canceled placement and break events to avoid stale or unintended data changes.
- Add the AGPL-3.0-only license for this plugin's source and include its text in the PHAR. BlockData keeps its LGPL license.

There is no intentional change to the stored record format or command permissions. To roll back, restore the previous plugin PHAR and retain a backup of the plugin's data folder. Data written by BlockData 1.0.1 uses the same LevelDB key format.
