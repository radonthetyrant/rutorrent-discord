# rutorrent-discord
A rutorrent plugin for discord webhook notifications (https://discordapp.com/developers/docs/resources/webhook)

Sends messages directly into a discord channel with configurable parameters:

![https://i.imgur.com/JMoa8H5.png](https://i.imgur.com/JMoa8H5.png)

And if discord is properly set up, it displays desktop notifications the moment a torrent is added, deleted or finished:

![https://i.imgur.com/yXkpbU1.png](https://i.imgur.com/yXkpbU1.png)

# Installation

* IMPORTANT: Make sure you have php-curl installed on the webserver that's hosting the ruTorrent install
* Download/clone the repo
* Add discordpush to your rutorrent/plugins directory
* Reload the web interface

# Usage
You need to get your webhook URL from your Discord server, see here for how to do it: https://support.discord.com/hc/en-us/articles/228383668-Intro-to-Webhooks
* In ruTorrent web interface, go to settings > Discord
* Paste the webhook url in the "Discord Webhook URL" box
* Choose if you want the "Ratio" to be included
* Enable / tick what events should be notified
* If you want to use Discord notifications on the iOS app, then you'll need to include your Discord user ID (so that the events 'mention' your user and therefore trigger a notification). See here for how to find your user ID: https://support.discord.com/hc/en-us/articles/206346498-Where-can-I-find-my-User-Server-Message-ID-

# Changelog
* **07.01.2023**
  * This has been updated to work with ruTorrent v4 (should still be compatible with v3)
  
* **01.07.2021**
  * This version now fixes the inclusion of the 'ratio' value
  * This also enables you to specifically 'mention' a Discord user in the pushed message (which enables proper iOS notifications)

* **10.04.2018**
  * Title now contains the torrent name, so that the discord desktop notification contains the complete Torrent title (was just "Torrent Finished/Deleted/Added" before)
  * Reduced the amount of fields in the discord embed (can be re-added in the plugins/discordpush/discord.php file)
  * Tracker now only displays the hostname without the torrent-key portion

* **05.04.2018**
  * Overhauled discord messages, now uses embed rather than a text message
  * Filesize numbers are now properly rounded
