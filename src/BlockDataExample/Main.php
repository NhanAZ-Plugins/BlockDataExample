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
		$this->blockData = BlockData::create($this, autoCleanup: false);

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onBlockPlace(BlockPlaceEvent $event) : void{
		if($event->isCancelled()){
			return;
		}
		$player = $event->getPlayer();

		foreach($event->getTransaction()->getBlocks() as [$x, $y, $z, $block]){
			$this->blockData->set($block, [
				"owner" => $player->getName(),
				"placed_at" => time(),
			]);
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void{
		if($event->isCancelled()){
			return;
		}
		$block = $event->getBlock();
		$player = $event->getPlayer();
		$data = $this->blockData->get($block);

		if($data === null){
			return;
		}

		$owner = is_array($data) ? ($data["owner"] ?? null) : null;
		if(!is_string($owner)){
			$this->blockData->remove($block);
			return;
		}

		if($player->getName() !== $owner && !$player->hasPermission("blockdata.bypass")){
			$player->sendMessage(TextFormat::RED . "This block belongs to " . TextFormat::WHITE . $owner . TextFormat::RED . "!");
			$event->cancel();
			return;
		}

		$this->blockData->remove($block);
		$player->sendMessage(TextFormat::GREEN . "Block data removed.");
	}

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
			$owner = is_array($data) ? ($data["owner"] ?? null) : null;
			$placedAt = is_array($data) ? ($data["placed_at"] ?? null) : null;
			if(!is_string($owner) || !is_int($placedAt)){
				$player->sendMessage(TextFormat::RED . "Stored block data has an invalid format.");
			}else{
				$time = date("Y-m-d H:i:s", $placedAt);
				$player->sendMessage(
					TextFormat::AQUA . "=== Block Info ===\n" .
					TextFormat::WHITE . "Owner: " . TextFormat::YELLOW . $owner . "\n" .
					TextFormat::WHITE . "Placed at: " . TextFormat::YELLOW . $time
				);
			}
		}

		$event->cancel();
	}

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
