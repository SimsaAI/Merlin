-- PostgreSQL schema for the ModelSyncExample project
-- Run: psql -U <user> -d <database> -f sql/postgresql.sql

CREATE TABLE IF NOT EXISTS users (
    id         SERIAL       NOT NULL,
    email      VARCHAR(255) NOT NULL,
    name       VARCHAR(100)     NULL,
    status     VARCHAR(20)  NOT NULL DEFAULT 'active',
    created_at TIMESTAMP    NOT NULL,
    updated_at TIMESTAMP        NULL,
    PRIMARY KEY (id)
);

COMMENT ON TABLE  users              IS 'Application users';
COMMENT ON COLUMN users.id           IS 'Primary key';
COMMENT ON COLUMN users.email        IS 'Unique e-mail address';
COMMENT ON COLUMN users.name         IS 'Display name';
COMMENT ON COLUMN users.status       IS 'Account status';
COMMENT ON COLUMN users.created_at   IS 'Record creation timestamp';
COMMENT ON COLUMN users.updated_at   IS 'Last update timestamp';

CREATE TABLE IF NOT EXISTS posts (
    id         SERIAL       NOT NULL,
    user_id    INTEGER      NOT NULL,
    title      VARCHAR(255) NOT NULL,
    content    TEXT             NULL,
    published  BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP    NOT NULL,
    PRIMARY KEY (id)
);

COMMENT ON TABLE  posts              IS 'User published posts';
COMMENT ON COLUMN posts.id           IS 'Primary key';
COMMENT ON COLUMN posts.user_id      IS 'Author user ID';
COMMENT ON COLUMN posts.title        IS 'Post title';
COMMENT ON COLUMN posts.content      IS 'Post body';
COMMENT ON COLUMN posts.published    IS 'Published flag';
COMMENT ON COLUMN posts.created_at   IS 'Record creation timestamp';

CREATE TABLE IF NOT EXISTS comments (
    id         SERIAL    NOT NULL,
    post_id    INTEGER   NOT NULL,
    user_id    INTEGER   NOT NULL,
    body       TEXT      NOT NULL,
    created_at TIMESTAMP NOT NULL,
    PRIMARY KEY (id)
);

COMMENT ON TABLE  comments            IS 'Post comments';
COMMENT ON COLUMN comments.id         IS 'Primary key';
COMMENT ON COLUMN comments.post_id    IS 'Parent post ID';
COMMENT ON COLUMN comments.user_id    IS 'Author user ID';
COMMENT ON COLUMN comments.body       IS 'Comment text';
COMMENT ON COLUMN comments.created_at IS 'Record creation timestamp';
