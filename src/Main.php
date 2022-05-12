<?php

declare(strict_types=1);

namespace tatchan\EasyKitAddActionToMineFlow;

use aieuo\mineflow\flowItem\FlowItemFactory;
use AndreasHGK\EasyKits\manager\KitManager;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{
    protected function onLoad(): void {
        FlowItemFactory::register(new EasyKitClaim());
    }
}
