# Icons

Koalati uses [FontAwesome 6](https://fontawesome.com/) for all of its icon needs.


## Subset 

The application only has a subset of the all the available icons in order to
keep file sizes small for the users, which improves loading times.

To add a new icon, you must:

1. Download [FontAwesome Pro Subsetter](https://fontawesome.com/v6/docs/web/dig-deeper/subsetter#contentHeader).
1. Load the subset configuration ([`DEV/fontawesome-subset.yaml`](/DEV/fontawesome-subset.yaml)).
1. Add the desired icon(s).
1. Save the subset configuration.
1. Generate the subset.
1. Overwrite the [`public/ext/fontawesome`](/public/ext/fontawesome) directory with the updated subset.

## Pro License

Please keep in mind that this project uses a Pro License of FontAwesome 6.

A Pro license is also required to download and use the Subsetter tool.
