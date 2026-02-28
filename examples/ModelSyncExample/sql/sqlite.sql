-- SQLite schema for the ModelSyncExample project
-- Run: sqlite3 sync_example.sqlite < sql/sqlite.sql
-- Note: SQLite does not support table or column comments.

CREATE TABLE IF NOT EXISTS users (
    id         INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
    email      TEXT     NOT NULL,
    name       TEXT,
    status     TEXT     NOT NULL DEFAULT 'active',
    gravatar_hash   TEXT,    
    created_at DATETIME NOT NULL,
    updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS posts (
    id         INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER  NOT NULL,
    title      TEXT     NOT NULL,
    content    TEXT,
    published  INTEGER  NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS comments (
    id         INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
    post_id    INTEGER  NOT NULL,
    user_id    INTEGER  NOT NULL,
    body       TEXT     NOT NULL,
    created_at DATETIME NOT NULL
);
