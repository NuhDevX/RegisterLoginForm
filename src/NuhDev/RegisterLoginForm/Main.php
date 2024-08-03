<?php

namespace NuhDev\RegisterLoginForm;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use NuhDev\RegisterLoginForm\libs\jojoe77777\FormAPI\{CustomForm, SimpleForm, ModalForm};
use pocketmine\command\{Command, CommandSender};
use NuhDev\RegisterLoginForm\libs\poggit\libasynql\{libasynql, DataConnector};

class Main extends PluginBase implements Listener {

    /** @var DataConnector */
    private $database;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->database = libasynql::create($this, $this->getConfig()->get("database"), [
            "sqlite" => "sqlite.sql",
        ]);

        $this->database->executeGeneric("users.create_table");
    }

    public function onDisable(): void {
        $this->database->close();
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
        $this->database->executeSelect("users.select_user", ["username" => $name], function(array $rows) use ($player, $name) {
            if (empty($rows)) {
                $this->showRegisterForm($player);
            } else {
                $this->showLoginForm($player, $rows[0]["password"]);
            }
        });
    }

    private function showRegisterForm(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) return;

            [$password, $confirmPassword] = $data;

            if ($password === $confirmPassword) {
                $name = $player->getName();
                $this->database->executeInsert("users.insert_user", [
                    "username" => $name,
                    "password" => $password,
                    "registered_at" => time()
                ]);
                $player->sendMessage("Successfully registered!");
            } else {
                $player->kick("Passwords do not match. Please register again.");
            }
        });

        $form->setTitle("Register");
        $form->addLabel("Please enter your password and confirm it.");
        $form->addInput("Password", "example: 12345", "");
        $form->addInput("Confirm Password", "example: 12345", "");
        $player->sendForm($form);
    }

    private function showLoginForm(Player $player, string $storedPassword): void {
        $form = new CustomForm(function (Player $player, ?array $data) use ($storedPassword) {
            if ($data === null) return;

            $password = $data[0];

            if ($password === $storedPassword) {
                $player->sendMessage("Successfully logged in!");
            } else {
                $player->kick("Wrong password. Please try again.");
            }
        });

        $form->setTitle("Login");
        $form->addLabel("Please enter your password. If you forgot it, contact an admin.");
        $form->addInput("Password", "Enter your password");
        $player->sendForm($form);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch ($command->getName()) {
            case "resetpassword":
                if ($sender instanceof Player) {
                    $this->showResetPasswordForm($sender);
                } else {
                    $sender->sendMessage("This command can only be used in-game.");
                }
                return true;
        }
        return false;
    }

    private function showResetPasswordForm(Player $admin): void {
        $this->database->executeSelect("users.select_all_users", [], function(array $rows) use ($admin) {
            $form = new SimpleForm(function (Player $admin, ?int $data) use ($rows) {
                if ($data === null) return;

                if (isset($rows[$data])) {
                    $this->showConfirmResetForm($admin, $rows[$data]["username"], $rows[$data]["password"], $rows[$data]["registered_at"]);
                }
            });

            $form->setTitle("Reset Password");
            $form->setContent("Select a player to reset their password.");

            foreach ($rows as $row) {
                $form->addButton($row["username"]);
            }

            $admin->sendForm($form);
        });
    }

    private function showConfirmResetForm(Player $admin, string $playerName, string $password, int $registeredAt): void {
        $form = new SimpleForm(function (Player $admin, ?int $data) use ($playerName) {
            if ($data === null) return;

            if ($data === 0) {
                $this->database->executeGeneric("users.delete_user", ["username" => $playerName]);
                $admin->sendMessage("Successfully reset password for $playerName.");
            } else { 
                $this->showResetPasswordForm($admin);
            }
        });

        $form->setTitle("Confirm Reset");
        $form->setContent("Player: $playerName\nPassword: $password\nRegistered at: " . date("Y-m-d H:i:s", $registeredAt));
        $form->addButton("Reset Password");
        $form->addButton("Back");

        $admin->sendForm($form);
    }
}
