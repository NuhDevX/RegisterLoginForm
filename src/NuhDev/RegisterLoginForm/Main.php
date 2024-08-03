<?php

namespace NuhDev\RegisterLoginForm;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\command\{Command, CommandSender};
use pocketmine\utils\Config;
use NuhDev\RegisterLoginForm\jojoe77777\FormAPI\SimpleForm;
use NuhDev\RegisterLoginForm\jojoe77777\FormAPI\CustomForm;
use NuhDev\RegisterLoginForm\jojoe77777\FormAPI\ModalForm;

class Main extends PluginBase implements Listener {

    private $passwords;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->passwords = new Config($this->getDataFolder() . "passwords.yml", Config::YAML);
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
        if (!$this->passwords->exists($name)) {
            $this->showRegisterForm($player);
        } else {
            $this->showLoginForm($player);
        }
    }

    private function showRegisterForm(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) return;

            [$password, $confirmPassword] = $data;

            if ($password === $confirmPassword) {
                $name = $player->getName();
                $this->passwords->set($name, [
                    "password" => $password,
                    "registered_at" => date("Y-m-d H:i:s")
                ]);
                $this->passwords->save();
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

    private function showLoginForm(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) return;

            $password = $data[0];
            $name = $player->getName();
            $storedPassword = $this->passwords->get($name)["password"];

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
        $form = new SimpleForm(function (Player $admin, ?int $data) {
            if ($data === null) return;

            $playerNames = array_keys($this->passwords->getAll());
            if (isset($playerNames[$data])) {
                $this->showConfirmResetForm($admin, $playerNames[$data]);
            }
        });

        $form->setTitle("Reset Password");
        $form->setContent("Select a player to reset their password.");

        foreach (array_keys($this->passwords->getAll()) as $name) {
            $form->addButton($name);
        }

        $admin->sendForm($form);
    }

    private function showConfirmResetForm(Player $admin, string $playerName): void {
        $data = $this->passwords->get($playerName);

        $form = new SimpleForm(function (Player $admin, ?int $data) use ($playerName) {
            if ($data === null) return;

            if ($data === 0) { 
                $this->passwords->remove($playerName);
                $this->passwords->save();
                $admin->sendMessage("Successfully reset password for $playerName.");
            } else { 
                $this->showResetPasswordForm($admin);
            }
        });

        $form->setTitle("Confirm Reset");
        $form->setContent("Player: $playerName\nPassword: {$data['password']}\nRegistered at: {$data['registered_at']}");
        $form->addButton("Reset Password");
        $form->addButton("Back");

        $admin->sendForm($form);
    }
}
