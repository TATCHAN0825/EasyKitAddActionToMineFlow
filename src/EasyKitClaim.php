<?php
declare(strict_types=1);

namespace tatchan\EasyKitAddActionToMineFlow;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\utils\KitException;

class EasyKitClaim extends FlowItem implements PlayerFlowItem {

    use PlayerFlowItemTrait;

    protected string $id = "EasyKitClaim";

    protected string $name = "EasyKitのキットを付与する";

    protected string $detail = "EasyKitのキットを付与する";

    protected array $detailDefaultReplace = ["player", "kit"];

    protected string $category = FlowItemCategory::PLAYER;

    public function __construct(
        private string $player = "",
        private string $kitName = "",
    ) {
        $this->setPlayerVariableName($this->player);
    }

    public function getKitName(): string {
        return $this->kitName;
    }

    public function setKitName(string $kitName): void {
        $this->kitName = $kitName;
    }


    public function isDataValid(): bool {
        return $this->getKitName() !== "";
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(),$this->getKitName()];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setKitName($content[1]);
        return $this;
    }

    public function execute(FlowItemExecutor $source) {
        $this->throwIfCannotExecute();
        $player = $this->getPlayer($source);
        $kitName = $source->replaceVariables($this->getKitName());
        $this->throwIfInvalidPlayer($player);

        $kit = KitManager::get($kitName);

        if ($kit === null) throw New InvalidFlowValueException($this->getName(),"kitNameが間違っています");
        try {
            $kit->claim($player);
        }catch (KitException $exception){
            throw new InvalidFlowValueException($this->getName(),"キット装備中にエラーが発生しました: " . $exception->getMessage());
        }


        yield true;

    }

    public function getEditFormElements(array $variables): array {
        $offset = array_search($this->getKitName(),array_keys(KitManager::getAll()),true);
        return [
            new PlayerVariableDropdown($variables,$this->getPlayerVariableName()),
            new Dropdown("EasyKitのキット名",array_keys(KitManager::getAll()),$offset === false ? 0 : $offset)
        ];
    }

    public function parseFromFormData(array $data): array {
        return [$data[0],array_keys(KitManager::getAll())[$data[1]]];
    }
}