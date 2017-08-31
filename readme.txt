=== Link2Post ===
Contributors: strangerstudios, dlparker1005, andrewza
Tags: cpt, gist, github
Requires at least: 4.8
Tested up to: 4.8.1
Stable tag: .1

Automatically parse submitted URLs to create posts.

== Description ==

Automatically parse submitted URLs to create posts.

== Installation ==

= Download, Install and Activate! =
1. Download the latest version of the plugin.
1. Unzip the downloaded file to your computer.
1. Upload the /link2post/ directory to the /wp-content/plugins/ directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Install and activate any plugins required for the embedding of specific modules. See each module below in the ‘Integrated Modules’ section to see the requirements for each module.

= How to Use =

1. Click on the checkbox next to ‘Show L2P’ in admin bar or navigate to Tools -> Link2Post from admin menu.
1. Enter a URL. Click ‘Create Post’
1. The URL will be parsed and added as a post or CPT based on the target site.
1. If a post was already created for that URL, you will be given the option to update the previously created post.

= Settings =

1. Navigate to Settings -> Link2Post from admin menu. Here you will see a list of all installed modules.
1. To enable the formatting of posts based on a given module, set ‘Format Post Content’ to ‘Yes’ for that module.
1. To create a CPT for a given module, set ‘Create and Use CPT’ to ‘Yes’ for that module. *Note: If ‘Format Post Content’ is set to ‘No’, ‘Create and Use CPT’ will also be set to ‘No’*
1. Click ‘Save Settings’


= Integrated Modules =

* YouTube (youtube.com).
* GitHub Gists (gist.github.com). Also install https://wordpress.org/plugins/oembed-gist/
* Codepen (codepen.io). Also install https://wordpress.org/plugins/codepen-embedded-pen-shortcode/
* jsfiddle (jsfiddle.net).

= Adding Modules =

1. Copy sample_module.php in the modules folder into your own custom plugin and rename the file to reflect your new module’s name
1. Follow the instructions given in the file that was sample_module.php to set up the module
1. If you'd like your module to be included in this plugin, you can do a pull request against the GitHub repository here: https://github.com/strangerstudios/link2post/


== Frequently Asked Questions ==

= I found a bug in the plugin. =

Please post it in the GitHub issue tracker here: https://github.com/strangerstudios/link2post/issues

== Changelog ==

= .1 =
* Initial version.