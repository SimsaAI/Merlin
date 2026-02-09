<?php
/**
 * SqlNode Usage Examples
 * 
 * This file demonstrates how to use the SqlNode system with query builders.
 * The SqlNode system extends the query builder to support complex SQL expressions
 * while maintaining backward compatibility with existing code.
 */

use CoreLib\Db\SqlNode;
use CoreLib\Db\InsertBuilder;
use CoreLib\Db\UpdateBuilder;
use CoreLib\Db\SelectBuilder;

// ============================================================================
// BASIC USAGE: Existing behavior is preserved
// ============================================================================

// Traditional way (still works exactly as before)
$qb = new InsertBuilder();
$qb->insert('posts')
    ->values(['title' => 'Hello', 'status' => 1])
    ->execute();
// SQL: INSERT INTO posts (title, status) VALUES ('Hello', 1)

// With bind parameters (still works as before)
$qb = new InsertBuilder();
$qb->insert('posts')
    ->bind(['title' => 'Hello', 'id' => 123])
    ->execute();
// SQL: INSERT INTO posts (title, id) VALUES (:title:, :id:)


// ============================================================================
// SQLNODE: Functions with literals (debug-friendly, no parameters)
// ============================================================================

$qb = new InsertBuilder();
$qb->insert('articles')
    ->values([
        'title' => 'My Article',
        'search' => SqlNode::func('to_tsvector', ['simple', 'My Article'])
    ])
    ->execute();
// SQL: INSERT INTO articles (title, search) 
//      VALUES ('My Article', to_tsvector('simple', 'My Article'))


// ============================================================================
// SQLNODE: Column references in functions
// ============================================================================

$qb = new UpdateBuilder();
$qb->update('articles')
    ->set('search', SqlNode::cast(
        SqlNode::func('to_tsvector', ['simple', SqlNode::column('title')]),
        'tsvector'
    ))
    ->where('id', 1)
    ->execute();
// PostgreSQL: UPDATE articles SET search = to_tsvector('simple', title)::tsvector WHERE (id = 1)
// MySQL: UPDATE articles SET search = to_tsvector('simple', title) CAST(tsvector) WHERE (id = 1)


// ============================================================================
// SQLNODE: Explicit bind parameters with SqlNode::param()
// ============================================================================

$qb = new UpdateBuilder();
$qb->update('articles')
    ->bind(['userId' => 42, 'timestamp' => time()])
    ->set([
        'updated_by' => SqlNode::param('userId'),
        'updated_at' => SqlNode::func('FROM_UNIXTIME', [SqlNode::param('timestamp')])
    ])
    ->where('id', 1)
    ->execute();
// SQL: UPDATE articles 
//      SET updated_by = :userId:, updated_at = FROM_UNIXTIME(:timestamp:)
//      WHERE (id = 1)


// ============================================================================
// SQLNODE: PostgreSQL arrays
// ============================================================================

$qb = new InsertBuilder();
$qb->insert('posts')
    ->values([
        'title' => 'PHP Tutorial',
        'tags' => SqlNode::pgArray(['php', 'programming', 'web'])
    ])
    ->execute();
// SQL: INSERT INTO posts (title, tags)
//      VALUES ('PHP Tutorial', '{"php","programming","web"}')


// ============================================================================
// SQLNODE: Complex expressions
// ============================================================================

$qb = new UpdateBuilder();
$qb->update('users')
    ->set([
        'full_name' => SqlNode::func('CONCAT', [
            SqlNode::column('first_name'),
            ' ',
            SqlNode::column('last_name')
        ]),
        'age' => SqlNode::func('TIMESTAMPDIFF', [
            SqlNode::raw('YEAR'),
            SqlNode::column('birthdate'),
            SqlNode::func('CURDATE', [])
        ])
    ])
    ->where('id', 123)
    ->execute();
// SQL: UPDATE users 
//      SET full_name = CONCAT(first_name, ' ', last_name),
//          age = TIMESTAMPDIFF(YEAR, birthdate, CURDATE())
//      WHERE (id = 123)


// ============================================================================
// SQLNODE: UPSERT with functions
// ============================================================================

$qb = new InsertBuilder();
$qb->insert('posts')
    ->values([
        'slug' => 'my-article',
        'title' => 'My Article',
        'view_count' => 0,
        'updated_at' => SqlNode::func('NOW', [])
    ])
    ->conflict(['slug'])  // PostgreSQL conflict target
    ->upsert([
        'title' => 'My Article',
        'view_count' => SqlNode::func('view_count', []) . ' + 1',  // Or use raw
        'updated_at' => SqlNode::func('NOW', [])
    ])
    ->execute();
// PostgreSQL: INSERT INTO posts (slug, title, view_count, updated_at)
//             VALUES ('my-article', 'My Article', 0, NOW())
//             ON CONFLICT (slug) DO UPDATE SET 
//                 title = 'My Article',
//                 view_count = view_count + 1,
//                 updated_at = NOW()


// ============================================================================
// SQLNODE: Raw SQL for edge cases
// ============================================================================

$qb = new InsertBuilder();
$qb->insert('logs')
    ->values([
        'message' => 'User logged in',
        'timestamp' => SqlNode::raw('CURRENT_TIMESTAMP'),
        'data' => SqlNode::raw("'[\"key\":\"value\"]'::jsonb")  // PostgreSQL JSONB
    ])
    ->execute();


// ============================================================================
// SQLNODE: WHERE clause with SqlNode
// ============================================================================

$qb = new SelectBuilder();
$qb->from('articles')
    ->where('tags && ?', SqlNode::pgArray(['php']))  // PostgreSQL array overlap
    ->execute();
// SQL: SELECT * FROM articles WHERE (tags && '{"php"}')


// ============================================================================
// SQLNODE: Mixing literals and parameters
// ============================================================================

$qb = new InsertBuilder();
$qb->insert('events')
    ->bind(['eventName' => 'user_login', 'userId' => 42])
    ->values([
        'event_type' => SqlNode::param('eventName'),
        'user_id' => SqlNode::param('userId'),
        'created_at' => SqlNode::func('NOW', []),
        'metadata' => SqlNode::func('JSON_OBJECT', [
            'ip',
            '192.168.1.1',
            'user_agent',
            'Mozilla/5.0'
        ])
    ])
    ->execute();
// SQL: INSERT INTO events (event_type, user_id, created_at, metadata)
//      VALUES (:eventName:, :userId:, NOW(), 
//              JSON_OBJECT('ip', '192.168.1.1', 'user_agent', 'Mozilla/5.0'))


// ============================================================================
// KEY PRINCIPLES
// ============================================================================

/*
1. BACKWARD COMPATIBILITY
   - Existing code continues to work without changes
   - values() with scalars → escaped literals in SQL
   - bind() → :placeholder: parameters as before
   - $bEscape parameter still respected

2. SQLNODE IS OPT-IN
   - Only use SqlNode when you need SQL expressions
   - For simple values, use regular values() as before
   - SqlNode makes intent explicit

3. DEFAULT BEHAVIOR: LITERALS
   - SqlNode::func() arguments are literals by default
   - Debug-friendly: you can see actual values in SQL
   - Good for internal queries and development

4. EXPLICIT PARAMETERS
   - Use SqlNode::param('name') to create bind parameters
   - Must also provide value via bind()
   - Good for user input and production queries

5. NO AMBIGUITY
   - Regular arrays → comma-separated list (for WHERE IN)
   - SqlNode::pgArray() → PostgreSQL array literal
   - Explicit is better than implicit

6. DRIVER-SPECIFIC
   - SqlNode handles MySQL vs PostgreSQL differences
   - Cast syntax: MySQL uses CAST(), PostgreSQL uses ::
   - You write once, it works everywhere
*/
