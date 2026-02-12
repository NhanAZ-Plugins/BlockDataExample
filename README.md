## BlockDataExample

A simple PocketMine-MP plugin that **shows how to use the [BlockData virion](https://github.com/NhanAZ-Libraries/BlockData)** by attaching extra information to blocks.

This plugin is mainly a **demo/example**, not a full protection system.

---

### What the plugin does

- **Remembers who placed a block**
  - When a player places a block, the plugin stores:
    - The player name
    - The time the block was placed
- **Only the owner can break the block**
  - If another player tries to break it, the break is cancelled (unless they have a bypass permission).
- **Lets you inspect block info**
  - Players can toggle an "inspect mode" and right-click a block to see:
    - Who placed it
    - When it was placed

All of this data is stored using the **BlockData** library — see the main repo:  
`https://github.com/NhanAZ-Libraries/BlockData`

---

### Requirements

- PocketMine-MP API `5.0.0` or newer
- The **BlockData** virion (library) loaded for this plugin  
  ([GitHub: NhanAZ-Libraries/BlockData](https://github.com/NhanAZ-Libraries/BlockData))

---

### Installation

1. Download or build the `BlockDataExample` plugin `.phar`.
2. Make sure the **BlockData** virion is correctly included in your server setup.
3. Put the `.phar` file into your server `plugins` folder.
4. Start (or restart) your PocketMine-MP server.

If everything is correct, you should see `BlockDataExample` enabled in the console.

---

### How to use

- **Place blocks**
  - Simply place any blocks as usual. Placement info will be saved automatically.

- **Inspect block data**
  - As a player, run:

    ```text
    /inspect
    ```

  - You will see a message saying inspect mode is enabled.
  - Right-click a block:
    - If it has data, you will see the owner name and placed time.
    - If not, it will say the block has no data.

- **Break blocks**
  - The block can only be broken by its owner (or players with the bypass permission).

---

### Permissions

- `blockdata.bypass`  
  Allows a player to break blocks regardless of who placed them.

You can give this permission to admins or trusted staff if you want them to ignore ownership checks.

---

### Notes

- This plugin is intentionally small and simple so you can:
  - Read the code easily (see `src/BlockDataExample/Main.php`).
  - Learn how to integrate and use the **BlockData** virion in your own plugins.
  - Compare with the official **BlockData** documentation here: `https://github.com/NhanAZ-Libraries/BlockData`

