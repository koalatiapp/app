# Framework detection 

When a project is created, the `StackDetector` service attempts to detect 
which framework / CMS / platform the website is built with.

This information is saved in the project's `tags` in the form of a string
matching the `App\Enum\Framework::` constants.


## Goals

The goal with this feature are to:

- provide more specific guidance to the users during the QA process;
- avoid asking them to do things that their platform prevents them from doing.


### Files of interest
- Controller(s): 
  - [`App\Controller\Project\ProjectCreationController`](/src/Controller/Project/ProjectCreationController.php) 
- Entities(s): [`App\Entity\Project`](/src/Entity/Project.php) 
- Services(s): [`App\Util\StackDetector`](/src/Util/StackDetector.php) 
- Templates: N/A
- Web components: N/A
