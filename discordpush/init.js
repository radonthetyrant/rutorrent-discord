//plugin.loadLang();
plugin.mark = 0;
plugin.hstTimeout = null;

plugin.actionNames = ['', '', '', ''];

if(plugin.canChangeOptions())
{
    plugin.addAndShowSettings = theWebUI.addAndShowSettings;
    theWebUI.addAndShowSettings = function( arg )
    {
        if(plugin.enabled)
        {
            $('#discord_webhook').val( theWebUI.discord.discord_webhook );
            $('#discord_avatar').val( theWebUI.discord.discord_avatar );
            $('#discord_pushuser').val( theWebUI.discord.discord_pushuser );
            $('#discord_mentionuser').val( theWebUI.discord.discord_pushover );
            $$('discord_enabled').checked = ( theWebUI.discord.discord_enabled != 0 );
            $$('discord_addition').checked = ( theWebUI.discord.discord_addition != 0 );
            $$('discord_finish').checked = ( theWebUI.discord.discord_finish != 0 );
            $$('discord_deletion').checked = ( theWebUI.discord.discord_deletion != 0 );

            $('#discord_enabled').change();

            //plugin.rebuildNotificationsPage();
        }
        plugin.addAndShowSettings.call(theWebUI,arg);
    }

    theWebUI.discordWasChanged = function()
    {
        return(($$('discord_enabled').checked != ( theWebUI.discord.discord_enabled != 0 )) ||
        ($$('discord_addition').checked != ( theWebUI.discord.discord_addition != 0 )) ||
        ($$('discord_finish').checked != ( theWebUI.discord.discord_finish != 0 )) ||
        ($$('discord_deletion').checked != ( theWebUI.discord.discord_deletion != 0 )) ||
        ($('#discord_avatar').val() != theWebUI.discord.discord_webhook) ||
        ($('#discord_pushuser').val() != theWebUI.discord.discord_webhook) ||
        ($('#discord_mentionuser').val() != theWebUI.discord.discord_mentionuser) ||
        ($('#discord_webhook').val() != theWebUI.discord.discord_webhook));
    }

    plugin.setSettings = theWebUI.setSettings;
    theWebUI.setSettings = function()
    {
        plugin.setSettings.call(this);
        if( plugin.enabled && this.discordWasChanged() )
            this.request( "?action=setdiscord" );
    }

    rTorrentStub.prototype.setdiscord = function()
    {
        this.content = "cmd=set" +
            "&discord_addition=" + ( $$('discord_addition').checked ? '1' : '0' ) +
            "&discord_deletion=" + ( $$('discord_deletion').checked  ? '1' : '0' ) +
            "&discord_finish=" + ( $$('discord_finish').checked  ? '1' : '0' ) +
            "&discord_enabled=" + ( $$('discord_enabled').checked  ? '1' : '0' ) +
            "&discord_avatar=" + $('#discord_avatar').val() +
            "&discord_pushuser=" + $('#discord_pushuser').val() +
            "&discord_mentionuser=" + $('#discord_mentionuser').val() +
            "&discord_webhook=" + $('#discord_webhook').val();

        this.contentType = "application/x-www-form-urlencoded";
        this.mountPoint = "plugins/discordpush/action.php";
        this.dataType = "script";
    }
}

if(plugin.canChangeTabs() || plugin.canChangeColumns())
{
    plugin.config = theWebUI.config;
    theWebUI.config = function(data)
    {
        plugin.config.call(theWebUI,data);
    }

    rTorrentStub.prototype.getdiscord = function()
    {
        this.content = "cmd=get&mark=" + plugin.mark;
        this.contentType = "application/x-www-form-urlencoded";
        this.mountPoint = "plugins/history/action.php";
        this.dataType = "json";
    }

    if(!$type(theWebUI.getTrackerName))
    {
        theWebUI.getTrackerName = function(announce)
        {
            var domain = '';
            if(announce)
            {
                var parts = announce.match(/^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/);
                if(parts && (parts.length>6))
                {
                    domain = parts[6];
                    if(!domain.match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/))
                    {
                        parts = domain.split(".");
                        if(parts.length>2)
                        {
                            if($.inArray(parts[parts.length-2]+"", ["co", "com", "net", "org"])>=0 ||
                                $.inArray(parts[parts.length-1]+"", ["uk"])>=0)
                                parts = parts.slice(parts.length-3);
                            else
                                parts = parts.slice(parts.length-2);
                            domain = parts.join(".");
                        }
                    }
                }
            }
            return(domain);
        }
    }

    if(plugin.canChangeMenu())
    {
        /*dxSTable.prototype.historySelect = function(e,id)
        {
            if(plugin.enabled && plugin.allStuffLoaded && (e.which==3))
            {
                var self = "theWebUI.getTable('"+this.prefix+"').";
                theContextMenu.clear();
                theContextMenu.add([theUILang.Remove, self+"cmdHistory('delete')"]);
                theContextMenu.show(e.clientX,e.clientY);
            }
        }*/
    }
}

if(plugin.canChangeMenu())
{

    plugin.createMenu = theWebUI.createMenu;
    theWebUI.createMenu = function( e, id )
    {
        plugin.createMenu.call(this, e, id);
        if(plugin.enabled && plugin.allStuffLoaded && theWebUI.discord.discord_enabled)
        {
            /*var table = this.getTable("trt");
            var el = theContextMenu.get(theUILang.peerAdd);
            if( el )
            {
                if(table.selCount==1)
                {
                    theContextMenu.add(el,[CMENU_CHILD, 'Pushbullet',
                        [
                            [ theUILang.turnNotifyOn, theWebUI.torrents[id].pushbullet ? "theWebUI.setPushbullet('')" : null ],
                            [ theUILang.turnNotifyOff, theWebUI.torrents[id].pushbullet ? null : "theWebUI.setPushbullet('1')" ]
                        ]]);
                }
                else
                {
                    theContextMenu.add(el,[CMENU_CHILD, 'Pushbullet',
                        [
                            [ theUILang.turnNotifyOn, "theWebUI.setPushbullet('1')" ],
                            [ theUILang.turnNotifyOff, "theWebUI.setPushbullet('')" ]
                        ]]);
                }
            }*/
        }
    }
}

plugin.onLangLoaded = function()
{
    injectScript(plugin.path+"/desktop-notify.js",function()
    {
        plugin.attachPageToOptions( $("<div>").attr("id","st_discord").html(
            "<fieldset>"+
            "<legend><a href='https://discordapp.com/developers/applications/me' target='_blank'>Discord Notifications</a></legend>"+
            "<div class='checkbox'>" +
            "<input type='checkbox' id='discord_enabled' onchange=\"linked(this, 0, ['discord_webhook','discord_avatar','discord_pushuser','discord_addition','discord_deletion','discord_finish','discord_mentionuser']);\"/>"+
            "<label for='discord_enabled'>Enabled</label>"+
            "</div>" +
            "<div>" +
            "<label for='discord_webhook' id='lbl_discord_webhook' class='disabled'>Discord Webhook URL</label>"+
            "<input type='text' id='discord_webhook' class='TextboxLarge' disabled='true' />"+
            "</div>" +
            "<div>" +
            "<label for='discord_avatar' id='lbl_discord_avatar' class='disabled'>Override Avatar URL</label>"+
            "<input type='text' id='discord_avatar' class='TextboxLarge' disabled='true' />"+
            "</div>" +
            "<div>" +
            "<label for='discord_pushuser' id='lbl_discord_pushuser' class='disabled'>Override Push Username</label>"+
            "<input type='text' id='discord_pushuser' class='TextboxLarge' disabled='true' />"+
            "</div>" +
            "<div>" +
            "<label for='discord_mentionuser' id='lbl_discord_mentionuser' class='disabled'>Discord Mention User ID</label>"+
            "<input type='text' id='discord_mentionuser' class='TextboxLarge' disabled='true' />"+
            "</div>" +
            "<div class='checkbox'>" +
            "<input type='checkbox' id='discord_addition' disabled='true' />"+
            "<label for='discord_addition' id='lbl_discord_addition' class='disabled'>Addition</label>"+
            "</div>" +
            "<div class='checkbox'>" +
            "<input type='checkbox' class='disabled' id='discord_deletion' disabled='true' />"+
            "<label for='discord_deletion' id='lbl_discord_deletion' class='disabled'>Deletion</label>"+
            "</div>" +
            "<div class='checkbox'>" +
            "<input type='checkbox' id='discord_finish' disabled='true' />"+
            "<label for='discord_finish' id='lbl_discord_finish' class='disabled'>Finish</label>"+
            "</div>" +
            "</fieldset>"
        )[0], "Discord" );
        plugin.actionNames = ['', "Added", "Finished", "Deleted"];
        plugin.markLoaded();
    });
}

plugin.onRemove = function()
{
    plugin.removePageFromOptions("st_discord");
    //theRequestManager.removeRequest( "trt", plugin.reqId1 );
}

/*plugin.langLoaded = function()
{
    if(plugin.enabled)
        plugin.onLangLoaded();
}*/
if(plugin.enabled)
    plugin.onLangLoaded();
