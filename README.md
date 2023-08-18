# Auto Flamingo Message Exporter

![Plugin Version](https://img.shields.io/badge/version-0.1-blue.svg)
[![Author](https://img.shields.io/badge/author-Raphael%20Borges-red.svg)](https://raphaelborges.com.br)
[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

## Description

The Flamingo Message Exporter WordPress plugin is designed to export message content from the Flamingo plugin to CSV files on your server.

## Features

- Export Flamingo messages to CSV files.
- Schedule automatic exports based on a predefined schedule.
- Copy the URLs of exported CSV files for external access.

## Installation

1. Download the plugin as a ZIP file from the GitHub repository.
2. In your WordPress admin panel, go to "Plugins" > "Add New" > "Upload Plugin."
3. Upload the downloaded ZIP file and activate the plugin.

## Usage

1. Install and activate the plugin on your WordPress site.
2. Navigate to the "Exportar Mensagens" (Export Messages) page in your WordPress admin panel.
3. Copy the links to the files for the lists you want to export.
4. Click the "Exportar" (Export) button to initiate the export process. You can also set up automatic exports using WordPress cron by waiting for the scheduled export.

### Customizing the Export Schedule

The plugin automatically schedules a daily export of messages. If you want to modify the export schedule, you can do so by editing the plugin code:

1. Open the plugin's main PHP file (`flamingo-csv-export.php`) in a code editor.
2. Find the `schedule_export_all_flamingo` function.
3. Modify the schedule using WordPress's cron interval values.
4. Save the changes.

## Customizing Automatic Export Behavior

The Flamingo Message Exporter plugin allows you to customize the automatic export behavior using code. By default, the plugin automatically exports messages whenever Contact Form 7 sends a submission. However, you might have specific scenarios where you want to disable this feature programmatically.

### Disabling Automatic Export via Code

If you wish to disable the automatic export feature via code, follow these steps:

1. Open your theme's `functions.php` file or create a custom plugin.
2. Add the following code snippet to the file:

```php
function disable_export_on_submission() {
    remove_action('wpcf7_mail_sent', 'export_all_flamingo_auto');
}
add_action('after_setup_theme', 'disable_export_on_submission');
```

## Compatibility

Please note that the Auto Flamingo Message Exporter plugin is designed to work specifically with the Contact Form 7 and Flamingo plugins. It leverages the functionalities provided by these plugins to export form messages. If you're using other form plugins, this plugin may not function as expected.


### License

This plugin is distributed under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0). You are free to use, modify, and distribute this plugin in accordance with the terms of the GPL v3.0.

[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
