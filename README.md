# odr-dabmux-announcements-controller
A PHP Script to control announcements on odr-dabmux (v1.1 or higher)

odr-dabmux is a open source implementation of a multiplexer for DAB / DAB+ Digital Radio (based on EN 300 401). Since version 0.8.0,
odr-dabmux has supported definition and control of announcement clusters. (See http://opendigitalradio.org).

An HTTP server can be installed on the same machine as odr-dabmux, to host this PHP script. The HTTP must currently be on the same
machine as odr-dabmux only allows local connections (127.0.0.1) to its interface. You might want to consider Apache or something
lighter. Please be sure to secure the HTTP server - this script has no inherent security features.

**CONFIGURATION**

You should read the documentation for odr-dabmux on how to configure the remote telnet interface (usually on port 12721) and how to
configure announcement clusters for individual stations. You must be using v1.1 or greater of odr-dabmux, which has support for the
start_in and stop_in attributes for announcement control.

When creating the clusters, you should use a consistent naming structure of:

  {station name}{common suffix}

such as:

  * station1_announcement
  * station2_announcement

You should confiure this common suffix in the variable

  $announcement_command_suffix

on line 6 of the PHP script. So in this example, amend line 6 to read

  $announcement_command_suffix = "_announcement";
  
The IP address and port of the target are preconfigured to match the defaults on odr-dabmux. Remember that odr-dabmux will reject non-local connections, so it's unlikely you'll ever change the IP address from "127.0.0.1".

You need to configure a value for $announcement_directory to allow the script to write flags and state. This must have read/writeable/delete access for the script.

**URL FORMAT**

You must pass one variable in the URL calling the script

  * station = the name of the station who's announcement cluster you want to control
  
You may pass additional variables in the URL calling the script
  * active = the state you want to set the announcement cluster to. Values are either 0 (inactive) or 1 (active). If missing, active is assumed = 0
  * delay = the number of milliseconds to wait before changing the flag state in the FIC channel. This is to allow for audio buffering delays. If missing, delay is assumed = 0
  
for example:

  http://127.0.0.1/announcements.php?station=station1&active=1&delay=3000

will set the announcements cluster for station1 active after a delay of 3 seconds.

**FREQUENCY OF UPDATES**

YThe interface into odr-dabmux is blocking (not multi threaded), so if the script is called whilst another instance of it is still running, it's likely to get blocked and held. Do that too much, and you'll end up with a lot of queued up scripts, and it will probably overwhelm the interface and cause it to hang.

The script tries to avoid overwhelming the interface by maintaining the current state of the traffic flag in a file in the $annoucements_directory. If the requested state is the same as the current state, the script will terminate early, without attaching to the odr-dabmux process.

**DE-BOUNCING**

This script implements a process for dealing with playout systems that may momentarily send an event that would cause the flag to be set inactive and then active again in a short period of time. This causes an annoying effect on the receiver. This script
waits 2 seconds before processing an inactive command, during which time a request to go active will prevent the inactive event
happening.

**HTTP RESPONSE CODES**

The script will return 3 codes:
  * 200 : The update appeared to process correctly
  * 400 : The syntax of the URL is incorrect
  * 500 : Another unknown error prevented the script completing correctly. Check you error logs.
  
**HTML OUTPUT**

There is a human readable HTML output to the script, so you can use it for debugging. Otherwise you can disregard the output of
the script.

**LOGGING**

Output is logged to syslog.

**KNOWN LIMITATIONS**

* It's possible to specify a station name incorrectly. The multplexer will throw an error saying it doesn't recognise the
  announcement cluster specified, but the script will fail silently, indicating an update happened correctly. So double
  check when you're configuring that you've got station names and $announcement_command_suffix correctly.
* The script has a 5 second time to deal with circumstances when the odr-dabmux interface has stalled. It's unlikely a local
  connection would ever be that slow, but be aware of this limitation.
* There's no range checking on the input values. You can specify values for "active" which are currently invalid on odr-dabmux.

_(c) 2018 Nick Piggott_

This is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This is distributed WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.
<http://www.gnu.org/licenses/>.
