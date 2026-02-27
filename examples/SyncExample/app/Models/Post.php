<?php

namespace App\Models;

use Merlin\Mvc\Model;

/**
 * Sync: php console.php sync model Models/Post.php --apply
 * With accessors: php console.php sync model Models/Post.php --apply --generate-accessors --field-visibility=protected
 */
class Post extends Model
{
    public int $id;
    public int $user_id;
    public string $title;
    public ?string $content;
    public int $published;
    public string $created_at;

    // Properties will be added automatically by the sync task.
}
