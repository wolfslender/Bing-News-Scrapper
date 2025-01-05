# Bing News Scraper

WordPress plugin that automatically searches and extracts news from Bing News based on specific keywords.

## Features

- Search news by keywords on Bing News
- Automatic extraction of article content
- Creation of post drafts with extracted content
- Simple and easy-to-use admin panel interface
- Limit of 3 articles per search to avoid overload

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Simple HTML DOM library (included)

## Installation

1. Download the plugin ZIP file
2. Go to your WordPress dashboard > Plugins > Add New
3. Click on "Upload Plugin" and select the ZIP file
4. Activate the plugin

## Usage

1. Go to "Bing News Scraper" in the admin panel sidebar
2. Enter a keyword in the search field
3. Click on "Search News"
4. The plugin will automatically create drafts with the found news
5. Review and edit the drafts before publishing

## Extracted Content Structure

Each created draft will include:
- News title
- Publication date
- News excerpt
- Main content
- Link to original source

## Important Notes

- Articles are created as drafts to allow review
- Original source links are preserved
- Extracted content is automatically cleaned from ads and unwanted elements
- It is recommended to review and edit content before publishing

## Troubleshooting

If you encounter any errors:
1. Verify that simple_html_dom.php is present in the plugin directory
2. Make sure you have database write permissions
3. Check WordPress error logs for more details

## Support

For technical support or to report issues, please contact:
[oliverodev.com](https://oliverodev.com.com)

## License

This plugin is free software and comes with no warranties.
