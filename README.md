## RegisterLoginForm Plugin

The **RegisterLoginForm** plugin for PocketMine-MP provides a registration and login system using custom forms. The plugin manages player registration and authentication, including functionality for resetting passwords by admins, utilizing the libasynql library for database management to improve performance.

### Features

1. **Player Registration**
   - When a player joins the server for the first time, a custom form appears asking them to enter and confirm their password.
   - The player’s credentials are saved in an SQLite database with their username, password, and registration timestamp.

2. **Player Login**
   - For returning players, a login form is displayed where they must enter their password.
   - If the password is correct, they are successfully logged in. If incorrect, the player is kicked from the server with a message indicating the password was incorrect.

3. **Admin Password Reset**
   - Admins can use the `/resetpassword` command to open a form listing registered players.
   - By selecting a player, admins can view their registration details and choose to reset their password.
   - If reset is confirmed, the player’s password is deleted from the database, and the admin is notified of the successful reset.

4. **Re-registration After Password Reset**
   - If a player joins the server again after their password has been reset, they are prompted to register a new password.
   - The new password is saved, and future logins will use this updated password.

### Installation

1. Ensure you have the FormAPI and libasynql plugins installed on your server.
2. Place the `RegisterLoginForm` plugin JAR file in the `plugins` directory of your PocketMine-MP server.
3. Create a `database.yml` file in the plugin data folder to configure the database connection.
4. Restart the server to enable the plugin.

### Commands

- `/resetpassword` - Opens a form for admins to reset

 player passwords.

### Database Configuration

- **database.yml** - This file configures the database connection settings for libasynql.

### Example

Upon first joining:
```
Custom Form:
Title: Register
Content: Please enter and confirm your password.
Input fields: Password, Confirm Password
```

Upon returning:
```
Custom Form:
Title: Login
Content: Please enter your password. If forgotten, contact an admin.
Input field: Password
```

Admin command usage:
```
/resetpassword
Simple Form: Select player to reset password
Simple Form: View player details and reset password
Modal Form: Confirm password reset
```
