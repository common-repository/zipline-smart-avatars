# Zipline Smart Avatars #
**Contributors:**      Zipline  
**Donate link:**       https://ziplinecommunities.com  
**Tags:**  
**Requires at least:** 5.8.0  
**Tested up to:**      6.4.1
**Requires PHP:**      7.3 or higher  
**Stable tag:**        1.1.1
**License:**           All rights reserved.  
**License URI:**       See license.txt

## Description ##

Instead of every user's avatar looking the same, Smart Avatars allows assigning a random avatar to new users during registration. Each user will be assigned a random avatar from your chosen avatar library.

Smart Avatars includes several avatar libraries for you to choose from, or go one step further and upload your own.

If you'd rather a consistent look, Smart Avatars also lets you assign every user the same avatar.

Smart Avatars is simple to install and use, requiring no additional coding or special theme support.

## Installation ##

1. Upload the entire `zl-smart-avatars` zip at `/wp-content/plugin-install.php`.

2. Activate the plugin through the **Plugins** screen (**Plugins > Installed Plugins**).

You will find the **Smart Avatars** menu in your WordPress admin screen inside the settings menu.

**BuddyPress must be installed for this plugin to function.**

### Manual Installation ###

1. Upload the entire `zl-smart-avatars` directory to the `/wp-content/plugins/` directory.
2. Activate Plugin Name through the 'Plugins' menu in WordPress.

Note: Web components will not load correctly if the plugin directory is not on URL `/wp-content/plugins/zl-smart-avatars`.

## Frequently Asked Questions ##

= Do images get deleted from the WP media library when they are cleared from the custom library? =

Not currently, they will still be in the media library and should you wish to delete them you can do so from there.

= Can I assign the same avatar for each user? =

* Leave the random avatar setting **off**
* Choose which of the displayed avatars you would like each user to have by clicking on the image.
* Click the **Save Changes** button.

= How do I assign an avatar from one of the pre-defined banks? =

* Turn the random avatar setting **on**.
* Selected either **Colours** or **Nature** using the radio buttons.
* Click the **Save Changes** button.

= How do I create a custom gallery of avatars and assigning one randomly? =

* Turn the random avatar setting **on**.
* Select the **Custom** option using the radio button.
* Click the **Add/Edit Gallery** button.
* Either click the **Select Files** button in the pop-up window or navigate to the **Add to gallery** tab on the left if there are already images in the media library that you'd like to use.
* Select the image files from a directory on your computer.
* Once they are uploaded click the **Update Gallery** button and the pop-up window will close.
* Click the **Save Changes** button.

= Can I edit my custom gallery? =

You certainly can!
</br>

* Click the **Add/Edit Gallery** button.
* Delete images from the gallery by clicking the cross overlayed on the image in the modal window that pops up. *Please note that this does not delete them from the media library, this has to be done manually through the media library page.*
* Click the **Update gallery** button.
* Alternatively, click the **Add to gallery tab** from the left-hand side and either add more from the media library or click the **Upload files** tab at the top of the modal and then **Select Files**.
* Once uploaded click the **Add to gallery** button, the **Update gallery** button and then the **Save Changes** button once you are back in the plugins main settings screen.
* You can clear all items from the gallery by pressing the **Clear Gallery** button and then the **Clear Gallery** button in the "Are You Sure" modal. *Please note that this does not delete them from the media library, this has to be done manually through the media library page.*

## Upgrade Notice ##

### 1.0.0 ###
* Feature: first Release.

### 1.1.0 ###
* Feature: Adds the ability for users to change their avatar, choosing from the selection defined in the admin panel.
* Feature: Adds the ability for user to upload their own avatar after registration.
* Fix: Get avatar location methods do not return base URL.
* Fix: Missing sanity checks on saving new avatar.
* Fix: Change avatar JS handlers execute twice.
* Fix: Custom galleries may not work if same filename is used over multiple months.
* Fix: AJAX update for users current avatar.
* Fix: Resize current avatar display as showing full size when using BuddyPress nouveau template.
* Feature: Adds the ability to assign a default user avatar from the inbuilt bank or add a custom avatar.
* Feature: Adds pro settings which are disabled unless pro version is installed. These are adding a default user cover image, group avatar and group cover image.
* Feature: Adds Cera theme support.

## 1.1.1
* Chore: Tests for PHP v8.2 and WordPress v6.4.1 compatibility.
