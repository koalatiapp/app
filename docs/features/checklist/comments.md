# Comments

## Goals
The goals for the comments system are to:

- help users track the work they have done and the work that needs to be done;
- improve communication between team members in the checklist;
- allow checklist tasks to be split into "subtasks" by the users.

## Implementation
Each checklist item has is an element on which users can comment.

Top-level comments are considered as threads that can be replied to and resolved.

Comment threads that haven't been resolved are indicated as "bug reports" on the project 
summary page, in order to attract attention to them (as they usually indicate things that
need to be fixed or worked on).

Comments are HTML-enabled, and the _wysiwyg_ editor used on the frontend is [TinyMCE](https://tiny.cloud).

All HTML is sanitized, and images are proxied to prevent any unwanted tracking of our users.

The comments are synced in real-time between all of the project's users via the 
Mercure protocol.

### Files of interest
- Controller(s): 
  - [`App\Controller\Api\CommentController`](/src/Controller/Api/CommentController.php) 
  - [`App\Controller\ProxyController`](/src/Controller/ProxyController.php)
- Entities(s): [`src\Entity\Comment`](/src/Entity/Comment.php) 
- Services(s): [`App\Util\HtmlSanitizer`](/src/Util/HtmlSanitizer.php) 
- Web components: [`assets/app/comment/*`](/assets/app/comment) 
- TinyMCE: [`public/ext/tinymce/*`](/public/ext/tinymce) 
