{
    "users.create_table": [
        "CREATE TABLE IF NOT EXISTS users (username TEXT PRIMARY KEY, password TEXT, registered_at INTEGER);"
    ],
    "users.select_user": [
        "SELECT * FROM users WHERE username = :username;"
    ],
    "users.insert_user": [
        "INSERT INTO users (username, password, registered_at) VALUES (:username, :password, :registered_at);"
    ],
    "users.delete_user": [
        "DELETE FROM users WHERE username = :username;"
    ],
    "users.select_all_users": [
        "SELECT * FROM users;"
    ]
}
