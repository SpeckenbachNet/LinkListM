# LinkListM

Link lists for Joomla 6, organised in categories. Show them on their own page, inside
articles or in a Custom module, as a list or as tiles.

English and German included.

## Features

- Links with title, URL, description, category, access level, language and publishing dates
- Categories and subcategories (Joomla's own category manager)
- Menu item "Link List": one or several categories, with or without subcategories,
  list or tiles (2 or 3 columns), description, domain, external links in a new window
- Placeholder `{linklistm ...}` for articles and Custom modules
- Editor button "Link list" that builds the placeholder for you
- Overview page in the backend: categories with link counts, to-dos, system check, changes
- Updates via System → Update

## Requirements

- Joomla 6.0 or later
- PHP 8.3 or later

## Installation

1. Download `pkg_linklistm-<version>.zip` from the
   [latest release](https://github.com/SpeckenbachNet/LinkListM/releases/latest).
2. In Joomla: **System → Install → Extensions**, upload the file.

The package installs the component, the content plugin and the editor button, and
switches both plugins on.

## Usage

1. **Components → LinkListM → Categories**: create your categories.
2. **Components → LinkListM → Links**: add links. A URL without `https://` is completed
   automatically.
3. Show the links in one of these ways.

### As a page

Create a menu item of type **LinkListM → Link List**. Its options are in the **Options** tab.

### In an article or a Custom module

Write a placeholder into the text:

```
{linklistm}                      all categories
{linklistm cat=3,5}              categories 3 and 5 with their subcategories
{linklistm cat=3 style=tiles}    as tiles
```

| Option      | Values              | Default     |
|-------------|---------------------|-------------|
| `cat`       | category IDs, `3,5` | all         |
| `sub`       | `1` / `0`           | `1`         |
| `style`     | `list` / `tiles`    | `list`      |
| `cols`      | `2` / `3`           | `2`         |
| `desc`      | `1` / `0`           | `1`         |
| `domain`    | `1` / `0`           | `1`         |
| `order`     | `ordering` / `title`| `ordering`  |
| `dir`       | `asc` / `desc`      | `asc`       |
| `newwindow` | `1` / `0`           | `1`         |

The category IDs are shown on the LinkListM overview page.

In a **Custom** module the option **Prepare Content** must be on (it is by default).

The editor button **Link list** below the editor opens a dialog: tick the categories,
choose the display, click **Insert**.

## Updates

Joomla checks this repository's releases for updates. New versions show up under
**System → Update → Extensions**.

## Uninstalling

Uninstalling the package removes all links (the table `#__linklistm_links`) and, as
Joomla does for every component, its categories. To keep your data, update instead of
uninstalling and reinstalling.

## Licence

GNU General Public License version 2 or later.

Speckenbach.net · software@speckenbach.net · https://software.speckenbach.net
