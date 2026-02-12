<?php

declare(strict_types=1);

namespace BlockDataExample;

use NhanAZ\BlockData\BlockData;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{

	private BlockData $blockData;

	/** @var array<string, bool> player name => inspect mode */
	private array $inspectMode = [];

	protected function onEnable() : void{
		// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
		// Setup BlockData — only ONE LINE needed!
		//
		// autoCleanup: false = handle data removal yourself (see onBlockBreak)
		// autoCleanup: true  = auto-remove when block is broken/exploded/burned
		// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
		$this->blockData = BlockData::create($this, autoCleanup: false);

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	// ── On block place: save placer info ────────────────────

	public function onBlockPlace(BlockPlaceEvent $event) : void{
		$player = $event->getPlayer();

		foreach($event->getTransaction()->getBlocks() as [$x, $y, $z, $block]){
			$this->blockData->set($block, [
				"owner" => $player->getName(),
				"placed_at" => time(),
			]);
		}
	}

	// ── On block break: check ownership ─────────────────────

	public function onBlockBreak(BlockBreakEvent $event) : void{
		$block = $event->getBlock();
		$player = $event->getPlayer();
		$data = $this->blockData->get($block);

		if($data === null){
			return; // No data on this block, allow breaking normally
		}

		$owner = $data["owner"];

		// Only the owner can break this block
		if($player->getName() !== $owner && !$player->hasPermission("blockdata.bypass")){
			$player->sendMessage(TextFormat::RED . "This block belongs to " . TextFormat::WHITE . $owner . TextFormat::RED . "!");
			$event->cancel();
			return;
		}

		// Owner is breaking their own block — clean up data
		$this->blockData->remove($block);
		$player->sendMessage(TextFormat::GREEN . "Block data removed.");
	}

	// ── Right-click to inspect block info ───────────────────

	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		if($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			return;
		}

		$player = $event->getPlayer();
		if(!isset($this->inspectMode[$player->getName()])){
			return;
		}

		$block = $event->getBlock();
		$data = $this->blockData->get($block);

		if($data === null){
			$player->sendMessage(TextFormat::GRAY . "This block has no data.");
		}else{
			$owner = $data["owner"];
			$time = date("Y-m-d H:i:s", $data["placed_at"]);
			$player->sendMessage(
				TextFormat::AQUA . "=== Block Info ===\n" .
				TextFormat::WHITE . "Owner: " . TextFormat::YELLOW . $owner . "\n" .
				TextFormat::WHITE . "Placed at: " . TextFormat::YELLOW . $time
			);
		}

		$event->cancel();
	}

	// ── /inspect command to toggle inspect mode ─────────────

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(!$sender instanceof Player){
			$sender->sendMessage("This command can only be used by players.");
			return true;
		}

		if($command->getName() === "inspect"){
			$name = $sender->getName();
			if(isset($this->inspectMode[$name])){
				unset($this->inspectMode[$name]);
				$sender->sendMessage(TextFormat::RED . "Inspect mode disabled.");
			}else{
				$this->inspectMode[$name] = true;
				$sender->sendMessage(TextFormat::GREEN . "Inspect mode enabled. Right-click a block to view its data.");
			}
			return true;
		}

		return false;
	}
}
