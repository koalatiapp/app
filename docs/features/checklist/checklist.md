# Checklist 

The checklist module guides users through the manual part of the quality control process.

Its current form is a simple checklist that is scoped to a project. 

The vision for the future of this module is to add a "wizard" process to make the
whole thing less daunting, and to better guide the users through each task.

## Goals
The checklist module's goals are to:

- simplify and speed up the QA process;
- make the QA process accessible to all developers.
- keep developers up-to-date on new best practices;

Here's how each of these goals is translated into actual features in the app:

- **Simplifying and speeding up the QA process** 
  - Users are guided through a curated checklist that covers all of the essentials with detailed descriptions.
  - Checklist progress is updated in real-time, allowing entire teams to work on the same QA at the same time.
  - _TODO:_ Every task that can be automated (partially or completely) is automated.
- **Making QA accessible to all developers**
  - Users simply have to follow the pre-determined tasks from the checklist sequentially.
  - Each task has a detailed description that doesn't assumptions about the user's skill level and knowledge.
  - More complex tasks contain link(s) to the best resource(s) to guide users through the steps.
  - Positive visual feedback is provided to the user whenever any progress is made.
- **Keeping developers up-to-date on best practices**
  - Koalati's recommended checklist template(s) are updated whenever a new best practice is made available to the development community.
  - Each task contains links to high-quality resources to help users understand the reasoning behind the task.

## Implementation
Each project has a checklist that is generated based on a JSON template provided by Koalati.

The `TemplateHydrator` service handles the "hydration" of the template into actual entities
for a given project. This happens automatically upon the first visit of the checklist page
for any given project.

On the frontend, the checklist is split into categories which contain the tasks themselves.

The checklist's progress is synced in real-time between all of the project's users via the 
Mercure protocol.

### Files of interest
- Controller(s): 
  - [`App\Controller\Project\ProjectChecklistController`](/src/Controller/Project/ProjectChecklistController.php) 
  - [`App\Controller\Api\Checklist\*`](/src/Controller/Api/Checklist) 
- Entities(s): [`App\Entity\Checklist\*`](/src/Entity/Checklist) 
- Services(s): [`App\Util\Checklist\*`](/src/Util/Checklist) 
- Templates: [`templates/app/project/checklist/`](/templates/app/project/checklist) 
- Web components: [`assets/app/checklist/`](/assets/app/checklist) 
