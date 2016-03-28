# odr-dabmux-announcements-controller
A PHP Script to control announcements on odr-dabmux

odr-dabmux is a open source implementation of a multiplexer for DAB / DAB+ Digital Radio (based on EN 300 401). Since version 0.8.0,
odr-dabmux has supported definition and control of announcement clusters. (See http://opendigitalradio.org)

An HTTP server can be installed on the same machine as odr-dabmux, to host this PHP script. The HTTP must currently be on the same
machine as odr-dabmux only allows local connections (127.0.0.1) to its interface. You might want to consider Apache or something
lighter. Please be sure to secure the HTTP server - this script has no inherent security features.

**CONFIGURATION**

You should read the documentation for odr-dabmux on how to configure the remote telnet interface (usually on port 12721) and how to
configure announcement clusters for individual stations.

When creating the clusters, you should use a consistent naming structure of:

  {station name}{common suffix}

such as:

  * station1_announcement
  * station2_announcement

You should confiure this common suffix in the variable

  $announcement_command_suffix

on line 6 of the PHP script. So in this example, amend line 6 to read

  $announcement_command_suffix = "_announcement";
  
The IP address and port of the target are preconfigured to match the defaults on odr-dabmux. Remember that odr-dabmux will reject
non-local connections, so it's unlikely you'll ever change the IP address from "127.0.0.1".

**URL FORMAT**

You must pass two variables in the URL calling the script

  * station = the name of the station who's announcement cluster you want to control
  * active = the state you want to set the announcement cluster to. Values are either 0 (inactive) or 1 (active).
  
for example:

  http://127.0.0.1/announcements.php?station=station1&active=1

will set the announcements cluster for station1 active.

**FREQUENCY OF UPDATES**

You should be careful when connecting this script up to a playout system which is capable of generating a lot of updates very quickly.
The interface into odr-dabmux is blocking (not multi threaded), so if the script is called whilst another instance of it is still
running, it's likely to get blocked and held. Do that too much, and you'll end up with a lot of queued up scripts, and it will
probably overwhelm the interface and cause it to hang.

It's recommended to put some sort of state caching layer in between a playout source and this script so that this script is only
called when a *change* of state is required, not just to asset the current state.

There is a PHP script named "announcements-caching-function.php" with an example function of such a caching approach in this repo. 

**HTTP RESPONSE CODES**

The script will return 3 codes:
  * 200 : The update appeared to process correctly
  * 400 : The syntax of the URL is incorrect
  * 500 : Another unknown error prevented the script completing correctly. Check you error logs.
  
**HTML OUTPUT**

There is a human readable HTML output to the script, so you can use it for debugging. Otherwise you can disregard the output of
the script.

**KNOWN LIMITATIONS**

* It's possible to specify a station name incorrectly. The multplexer will throw an error saying it doesn't recognise the
  announcement cluster specified, but the script will fail silently, indicating an update happened correctly. So double
  check when you're configuring that you've got station names and $announcement_command_suffix correctly.
* The script has a 5 second time to deal with circumstances when the odr-dabmux interface has stalled. It's unlikely a local
  connection would ever be that slow, but be aware of this limitation.
* There's no range checking on the input values. You can specify values for "active" which are currently invalid on odr-dabmux.

_(c) 2016 Nick Piggott_

This is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This is distributed WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.
<http://www.gnu.org/licenses/>.
