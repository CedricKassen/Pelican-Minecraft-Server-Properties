# Minecraft Server Properties (by Cedric Kassen)

Edit the `server.properties` of your Minecraft server through a friendly form, no need to touch the file yourself.

## Setup

The page is shown for every server whose egg has the `minecraft` _tag_ (proxies with the `proxy` tag are excluded, they have no `server.properties`).
To enable it for other eggs, add `server_properties` to the _features_ of the egg.

Subusers need the `file.read-content` permission to see the page and `file.update` to save changes. _Save & Restart_ additionally requires `control.restart`.

## Features

- **Grouped Settings**: General, Players & Access, World, Performance, Resource Pack and Advanced, with labels, help texts and units instead of raw keys
- **Version Aware**: Only properties that exist in the server's file are shown, so it fits every Minecraft version
- **MOTD Preview**: Live preview of the message of the day, including color and format codes, with a picker to insert them
- **Safe Saving**: Only changed properties are written, the file is read again right before saving and comments, order and unknown properties are kept
- **First Start Hint**: Shows a hint if Minecraft hasn't written its settings into the file yet (e.g. a brand new server)
- **Panel Managed Properties**: Properties the egg sets on every start (like `server-port`) are shown read-only
- **Locked Properties**: Admins can lock properties (e.g. `max-players` when slots are sold) in the plugin settings
- **Save & Restart**: Apply your changes with one click while the server is running
- **Multilingual**: Supports English and German translations

## Not editable on purpose

`server-ip`, `server-port` and `query.port` are set by the panel from the server's allocation, anything `rcon`, `jmx` or `management-server` related grants remote control over the server and is left out, as are debug and JSON only settings.
Those can still be changed via the file manager, the page links to it.

## Configuration

Locked properties can be set in the plugin settings or via the `MINECRAFT_SERVER_PROPERTIES_LOCKED` environment variable (comma separated, e.g. `max-players,online-mode`).
