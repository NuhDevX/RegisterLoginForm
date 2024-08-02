<?php

namespace ZhaDev\RegisterLoginForm;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use jojoe77777\FormAPI\{CustomForm, SimpleForm, ModalForm};
use pocketmine\command\{Command, CommandSender};
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

    private $passwords;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
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

        foreachh (array_keys($this->passwords->getAll()) as $name) {
            $form->addButton($name)
        }

        $admin->sendForm($form);
    }

    private function showConfirmResetForm(Player $admin, string $playerName): void {
        $data = $this->passwords->get($playerName);

        $form = new SimpleForm(function (Player $admin, ?int $data) use ($playerName) {
            if ($data === null) return;

            if ($data === 0) { // Reset password
                $this->passwords->remove($playerName);
                $this->passwords->save();
                $admin->sendMessage("Successfully reset password for $playerName.");
            } else { // Back
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
