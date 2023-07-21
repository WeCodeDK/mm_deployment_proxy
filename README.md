## MatterMost deployment proxy

This is a simple proxy for Laravel Forge deployment notifications to MatterMost.

### Installation
Set `MATTERMOST_HOST=` to point to your MatterMost instance.

### Usage
Point your Forge deployment notification to the URL of this script
```
https://mm-deployment-proxy.com/{webhookId}/{channelName}
```
where `{webhookId}` is the ID of the MatterMost webhook and `{channelName}` is the name of the channel to post to.
Use the channel’s name and not the display name, e.g. use town-square, not Town Square.
